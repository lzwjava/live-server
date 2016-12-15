<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/9/16
 * Time: 5:41 AM
 */
class RecordedVideos extends BaseController
{
    public $liveDao;
    public $recordedVideos;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(RecordedVideoDao::class);
        $this->recordedVideos = new RecordedVideoDao();
    }

    function one_get()
    {

    }

    function list_get($liveId)
    {
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $list = $this->recordedVideos->getVideosByLiveId($liveId);
        $this->succeed($list);
    }

    private function copyVideos($videos)
    {
        $this->mkdirIfNotExists(VIDEO_WORKING_DIR);
        foreach ($videos as $video) {
            $ok = $this->copyFromCheerHost(ORIGIN_VIDEO_DIR . $video->fileName,
                VIDEO_WORKING_DIR . $video->fileName);
            if (!$ok) {
                return false;
            }
        }
        return true;
    }

    private function copyFromCheerHost($fromFile, $toFile)
    {
        logInfo("begin scp $fromFile $toFile");
        $conn = ssh2_connect('cheer.quzhiboapp.com', 22);
        ssh2_auth_password($conn, 'root', CHEER_HOST_PASSWORD);
        $ok = ssh2_scp_recv($conn, $fromFile,
            $toFile);
        if (!$ok) {
            logInfo("scp succeed");
        } else {
            logInfo("scp failed");
        }
        return $ok;
    }

    private function mkdirIfNotExists($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    private function copyReplayVideo($video)
    {
        $this->mkdirIfNotExists(REPLAY_WORKING_DIR);
        $ok = $this->copyFromCheerHost(ORIGIN_VIDEO_DIR . $video->fileName,
            REPLAY_WORKING_DIR . $video->fileName);
        return $ok;
    }

    function replay_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_LIVE_ID))) {
            return;
        }
        $liveId = $this->get(KEY_LIVE_ID);
        $live = $this->liveDao->getRawLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $videos = $this->recordedVideos->getVideosAfterPlanTs($liveId, $live->planTs);
        if (count($videos) == 0) {
            $this->failure(ERROR_VIDEOS_NOT_GEN);
            return;
        }
        $lastVideo = $videos[count($videos) - 1];
        $ok = $this->copyReplayVideo($lastVideo);
        if (!$ok) {
            $this->failure(ERROR_SCP_FAIL);
            return;
        }
        $localFile = REPLAY_WORKING_DIR . $lastVideo->fileName;
        for (; ;) {
            $live = $this->liveDao->getRawLive($liveId);
            if ($live->status != LIVE_STATUS_OFF) {
                $rtmpUrl = 'rtmp://cheer.quzhiboapp.com/live/' . $live->rtmpKey;
                $outputArr = array();
                $returnVar = null;
                $ffmpeg = FFMPEG_PATH;
                $command = "$ffmpeg -re -i $localFile -vcodec copy -acodec copy -f flv -y $rtmpUrl";
                logInfo($command);
                exec($command, $outputArr, $returnVar);
                logInfo("ffmpeg return var: $returnVar");
            } else {
                logInfo("live status off, transcoded finish");
                break;
            }
            sleep(1);
        }
        $this->succeed();
    }

    function convert_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_LIVE_ID))) {
            return;
        }
        $liveId = $this->get(KEY_LIVE_ID);
        $live = $this->liveDao->getRawLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $videos = $this->recordedVideos->getVideosAfterPlanTs($liveId, $live->planTs);
        if (count($videos) == 0) {
            $this->failure(ERROR_VIDEOS_NOT_GEN);
            return;
        }
        $ok = $this->copyVideos($videos);
        if (!$ok) {
            $this->failure(ERROR_FAIL_HANDLE_VIDEO);
            return;
        }
        $ok = $this->convertVideos($videos);
        if (!$ok) {
            $this->failure(ERROR_CONVERT_VIDEO);
            return;
        }
        $newVideos = $this->recordedVideos->getVideosAfterPlanTs($liveId, $live->planTs);
        if (count($newVideos) > 1) {
            $ok = $this->mergeVideos($newVideos, $live);
            if (!$ok) {
                $this->failure(ERROR_MERGE_VIDEO);
                return;
            }
            $mp4File = VIDEO_WORKING_DIR . $live->rtmpKey . '.mp4';
        } else {
            $mp4File = VIDEO_WORKING_DIR . $newVideos[0]->transcodedFileName;
        }

        $conn = ssh2_connect('video.quzhiboapp.com', 22);
        ssh2_auth_password($conn, 'root', 'Quzhiboapp1314');
        $ok = ssh2_scp_send($conn, $mp4File, NGINX_VIDEO_DIR . $live->rtmpKey . '.mp4');
        if (!$ok) {
            $this->failure(ERROR_SCP_FAIL);
            return;
        }
        if ($live->status == LIVE_STATUS_TRANSCODE) {
            $this->liveDao->endLive($live->liveId);
        }
        $this->succeed();
    }

    private function pureFileName($filename)
    {
        $output = array();
        $res = preg_match('/(.*)\.flv/', $filename, $output);
        if (!$res) {
            return null;
        }
        return $output[1];
    }

    private function convertVideos($videos)
    {
        $allOk = true;
        foreach ($videos as $video) {
            if (!$video->transcoded) {
                $fileName = $video->fileName;
                $pureFileName = $this->pureFileName($fileName);
                $input = VIDEO_WORKING_DIR . $fileName;
                $newFileName = $pureFileName . '.mp4';
                $output = VIDEO_WORKING_DIR . $newFileName;
                $outputArr = array();
                $returnVar = null;
                $ffmpeg = FFMPEG_PATH;
                logInfo("$ffmpeg -i $input -y $output");
                exec("$ffmpeg -i $input -y $output", $outputArr, $returnVar);
                logInfo("convert video return var: $returnVar");
                if ($returnVar != 0) {
                    $allOk = false;
                } else {
                    $this->recordedVideos->updateVideoToTranscoded($fileName, $newFileName);
                }
            } else {
                logInfo("video already transcoded");
            }
        }
        return $allOk;
    }

    private function convertVideos1($videos)
    {
        $allOk = true;
        foreach ($videos as $video) {
            try {
                $fileName = $video->fileName;
                $pureFileName = $this->pureFileName($fileName);
                $ffmpeg = FFMpeg\FFMpeg::create();
                $video = $ffmpeg->open(VIDEO_WORKING_DIR . $fileName);
                $format = new FFMpeg\Format\Video\X264('libvo_aacenc');
                $format->on('progress', function ($video, $format, $percentage) {
                    logInfo("$percentage % transcoded");
                });
                $newFileName = $pureFileName . '.mp4';
                $video->save($format, VIDEO_WORKING_DIR . $newFileName);
                $this->recordedVideos->updateVideoToTranscoded($fileName, $newFileName);
                logInfo("convert $fileName finish");
            } catch (Exception $e) {
                $allOk = false;
                logInfo('video convert exception:' . $e->getMessage());
            }
        }
        return $allOk;
    }

    private function mergeVideos($videos, $live)
    {
        $mergeText = '';
        $concatFile = VIDEO_WORKING_DIR . $live->rtmpKey . '.txt';
        $mp4File = $live->rtmpKey . '.mp4';
        $outputFile = VIDEO_WORKING_DIR . $mp4File;
        foreach ($videos as $video) {
            $mergeText .= "file '" . $video->transcodedFileName . "'\n";
        }
        file_put_contents($concatFile, $mergeText);
        $output = array();
        $returnVar = null;
        $ffmpeg = FFMPEG_PATH;
        logInfo("$ffmpeg -f concat -i $concatFile -c copy -y  $outputFile");
        exec("$ffmpeg -f concat -i $concatFile -c copy -y  $outputFile", $output, $returnVar);
        logInfo("concat return var " . $returnVar);
        return !$returnVar;
    }

}
