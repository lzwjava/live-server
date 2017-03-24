<?php


class WechatEventsDao extends BaseDao
{
    public function addWechatEvent($eventType, $openId, $userId)
    {
        $data = array(
            KEY_EVENT_TYPE => $eventType,
            KEY_OPEN_ID => $openId,
            KEY_USER_ID => $userId
        )
        $this->db->insert(TABLE_WECHAT_EVENTS, $data);
        return $this->db->insert_id();
    }

    public function getUserEvents($userId)
    {
        return $this->getListFromTable(TABLE_WECHAT_EVENTS, 'userId', $userId);
    }
}
