<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/9/16
 * Time: 5:41 AM
 */
class Videos extends BaseController
{
    public $liveDao;
    public $videoDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(VideoDao::class);
        $this->videoDao = new VideoDao();
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
        $list = $this->videoDao->getVideosByLiveId($liveId);
        $this->succeed($list);
    }

    private function copyVideos($videos)
    {
        foreach ($videos as $video) {
            $conn = ssh2_connect('cheer.quzhiboapp.com', 22);
            ssh2_auth_password($conn, 'root', 'Quzhiboapp2046');
            $ok = ssh2_scp_recv($conn, ORIGIN_VIDEO_DIR . $video->fileName,
                VIDEO_WORKING_DIR . $video->fileName);
            if (!$ok) {
                return false;
            }
        }
        return true;
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
        $videos = $this->videoDao->getVideosAfterPlanTs($liveId, $live->planTs);
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
        $newVideos = $this->videoDao->getVideosAfterPlanTs($liveId, $live->planTs);
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
        ssh2_scp_send($conn, $mp4File, NGINX_VIDEO_DIR . $live->rtmpKey . '.mp4');
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
            $fileName = $video->fileName;
            $pureFileName = $this->pureFileName($fileName);
            $input = VIDEO_WORKING_DIR . $fileName;
            $newFileName = $pureFileName . '.mp4';
            $output = VIDEO_WORKING_DIR . $newFileName;
            $outputArr = array();
            $returnVar = null;
            exec("ffmpeg -i $input -y $output", $outputArr, $returnVar);
            if ($returnVar != 0) {
                $allOk = false;
            }
            $this->videoDao->updateVideoToTranscoded($fileName, $newFileName);
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
                $this->videoDao->updateVideoToTranscoded($fileName, $newFileName);
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
        exec('ffmpeg -f concat -i ' . $concatFile . ' -c copy -y ' . $outputFile, $output, $returnVar);
        return !$returnVar;
    }

}
