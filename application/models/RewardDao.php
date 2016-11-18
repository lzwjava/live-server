<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/19/16
 * Time: 7:21 AM
 */
class RewardDao extends BaseDao
{
    function addReward($userId, $liveId, $orderNo)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_LIVE_ID => $liveId,
            KEY_ORDER_NO => $orderNo
        );
        $this->db->insert(TABLE_REWARDS, $data);
        return $this->db->insert_id();
    }

}
