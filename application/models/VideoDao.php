<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/9/16
 * Time: 4:55 AM
 */
class VideoDao extends BaseDao
{

    private function endTsByFileName($fileName)
    {
        $output = array();
        $res = preg_match('/.*\.([0-9]*)\.flv/', $fileName, $output);
        if (!$res) {
            return null;
        }
        return $output[1];
    }

    function addVideo($liveId, $fileName)
    {
        $endTs = $this->endTsByFileName($fileName);
        $data = array(
            KEY_LIVE_ID => $liveId,
            KEY_FILE_NAME => $fileName,
            KEY_END_TS => $endTs
        );
        $this->db->insert(TABLE_VIDEOS, $data);
        return $this->db->insert_id();
    }

    function getVideosByLiveId($liveId)
    {
        return $this->getListFromTable(TABLE_VIDEOS, KEY_LIVE_ID, $liveId);
    }

}
