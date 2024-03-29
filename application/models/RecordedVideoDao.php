<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/9/16
 * Time: 4:55 AM
 */
class RecordedVideoDao extends BaseDao
{

    private function beginTsByFileName($fileName)
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
        $beginTs = $this->beginTsByFileName($fileName);
        $data = array(
            KEY_LIVE_ID => $liveId,
            KEY_FILE_NAME => $fileName,
            KEY_BEGIN_TS => $beginTs
        );
        $this->db->insert(TABLE_RECORDED_VIDEOS, $data);
        return $this->db->insert_id();
    }

    function getVideosByLiveId($liveId)
    {
        return $this->getListFromTable(TABLE_RECORDED_VIDEOS, KEY_LIVE_ID, $liveId);
    }

    function getVideosAfterPlanTs($liveId, $planTs)
    {
        $dateTime = date_create($planTs, new DateTimeZone('Asia/Shanghai'));
        $planTsNum = ($dateTime->getTimestamp() - 60 * 30) * 1000;
        $sql = "SELECT v.*,l.rtmpKey FROM recorded_videos AS v
                LEFT JOIN lives AS l ON l.liveId=v.liveId
                WHERE v.liveId=? AND v.beginTs > ? ORDER BY v.beginTs";
        $binds = array($liveId, $planTsNum);
        return $this->db->query($sql, $binds)->result();
    }

    function updateVideoToTranscoded($filename, $newFileName)
    {
        $sql = "UPDATE recorded_videos SET transcoded=1, transcodedTime=now(),
                transcodedFileName=? WHERE fileName=?";
        $binds = array($newFileName, $filename);
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

}
