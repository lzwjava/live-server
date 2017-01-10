<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/14/16
 * Time: 3:03 AM
 */
class VideoDao extends BaseDao
{
    function addVideo($liveId, $title, $fileName)
    {
        $data = array(
            KEY_LIVE_ID => $liveId,
            KEY_TITLE => $title,
            KEY_FILE_NAME => $fileName
        );
        $this->db->insert(TABLE_VIDEOS, $data);
        return $this->db->insert_id();
    }

    function addVideoByLive($live)
    {
        return $this->videoDao->addVideo($live->liveId, $live->subject, $live->rtmpKey);
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
        foreach ($videos as $video) {
            $host = $this->electVideoHost();
            $video->url = $host . $video->fileName . '.mp4';
        }
    }

}
