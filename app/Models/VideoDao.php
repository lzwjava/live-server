<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/14/16
 * Time: 3:03 AM
 */
class VideoDao extends BaseDao
{
    protected $table = 'videos';

    function addVideo($liveId, $title, $fileName, $type)
    {
        $data = array(
            KEY_LIVE_ID => $liveId,
            KEY_TITLE => $title,
            KEY_FILE_NAME => $fileName,
            KEY_TYPE => $type
        );
        $this->db->table(TABLE_VIDEOS)->insert($data);
        return $this->db->insertID();
    }

    function addVideoByLive($live)
    {
        return $this->addVideo($live->liveId, $live->subject, $live->rtmpKey, VIDEO_TYPE_M3U8);
    }

    function getVideosByLiveId($liveId)
    {
        $videos = $this->getListFromTable(TABLE_VIDEOS, KEY_LIVE_ID, $liveId, '*', KEY_FILE_NAME);
        $this->assembleVideos($videos);
        return $videos;
    }

    private function electVideoHost()
    {
//        return random_element(array(VIDEO_HOST_URL, VIDEO_ALI_HOST_URL));
        return random_element(array(VIDEO_HOST_URL));
    }

    private function assembleVideos($videos)
    {
        $host = $this->electVideoHost();
        foreach ($videos as $video) {
            if ($video->type == VIDEO_TYPE_MP4) {
                $video->url = $host . $video->fileName . '.mp4';
            } else if ($video->type == VIDEO_TYPE_M3U8) {
                $prefix = 'http://ojaulfft5.bkt.clouddn.com/recordings/z1.qulive.';
                if ($video->fileName == 'sQ91eVEh') {
                    $video->m3u8Url = $prefix . $video->fileName . '/playback1.m3u8';
                } else {
                    $video->m3u8Url = $prefix . $video->fileName . '/playback.m3u8';
                }
            }
        }
    }

    function mp4Ready($liveId)
    {
        return $this->db->table(TABLE_VIDEOS)->where(KEY_LIVE_ID, $liveId)->update(array(KEY_TYPE => VIDEO_TYPE_MP4)) !== false;
    }

}

// Namespace bridge: allow App\Libraries\VideoDao → App\Models\VideoDao
class_alias('App\Models\VideoDao', 'App\Libraries\VideoDao');
