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

    private function publicFields()
    {
        return array(KEY_REWARD_ID, KEY_USER_ID, KEY_LIVE_ID, KEY_ORDER_NO, KEY_CREATED, KEY_UPDATED);
    }

    private function publicFieldsStr($prefix = TABLE_REWARDS, $alias = false)
    {
        return $this->mergeFields($this->publicFields(), $prefix, $alias);
    }

    function getList($liveId)
    {
        $rewardFields = $this->publicFieldsStr('r');
        $userFields = $this->userPublicFields('u', true);
        $sql = "SELECT $rewardFields,c.amount, $userFields FROM rewards as r
               left join users as u on u.userId=r.userId
               left join charges as c on r.orderNo = c.orderNo
               where r.liveId=?
               order by r.created desc";
        $binds = array(KEY_LIVE_ID => $liveId);
        $rewards = $this->db->query($sql, $binds)->result();
        $this->assembleRewards($rewards);
        return $rewards;
    }

    private function assembleRewards($rewards)
    {
        foreach ($rewards as $reward) {
            $us = $this->prefixFields($this->userPublicRawFields(), 'u');
            $reward->user = extractFields($reward, $us, 'u');
        }
        return $rewards;
    }

}
