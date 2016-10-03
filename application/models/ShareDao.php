<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 10/4/16
 * Time: 4:46 AM
 */
class ShareDao extends BaseDao
{
    function addShare($userId, $liveId, $shareTs, $channel)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_LIVE_ID => $liveId,
            KEY_SHARE_TS => $shareTs,
            KEY_CHANNEL => $channel
        );
        return $this->db->insert(TABLE_SHARES, $data);
    }

    function getShare($userId, $liveId)
    {
        $binds = array($userId, $liveId);
        return $this->db->query("SELECT * FROM shares WHERE userId=? AND liveId=?", $binds)->row();
    }

    function useToDiscount($userId, $liveId)
    {
        $binds = array($userId, $liveId);
        return $this->db->query("UPDATE shares SET useToDiscount=1 WHERE userId=? AND liveId=?", $binds);
    }
}
