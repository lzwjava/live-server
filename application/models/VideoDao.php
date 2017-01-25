<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/14/16
 * Time: 3:03 AM
 */
class VideoDao extends BaseDao
{
    function addVideo($liveId, $title, $fileName, $type)
    {
        $data = array(
            KEY_LIVE_ID => $liveId,
            KEY_TITLE => $title,
            KEY_FILE_NAME => $fileName,
            KEY_TYPE => $type
        );
        $this->db->insert(TABLE_VIDEOS, $data);
        return $this->db->insert_id();
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
                $video->m3u8Url = 'http://ojaulfft5.bkt.clouddn.com/recordings/z1.qulive.'
                    . $video->fileName . '/playback.m3u8';
            }
        }
    }

}
