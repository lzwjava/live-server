<?php

namespace App\Models;

use CodeIgniter\Model;


class WechatEventsDao extends BaseDao
{
    protected $table = 'wechat_events';

    public function addWechatEvent($eventType, $openId, $userId)
    {
        $data = array(
            KEY_EVENT_TYPE => $eventType,
            KEY_OPEN_ID => $openId,
            KEY_USER_ID => $userId
        );
        $this->db->table(TABLE_WECHAT_EVENTS)->insert($data);
        return $this->db->insertID();
    }

    public function getUserEvents($userId)
    {
        return $this->getListFromTable(TABLE_WECHAT_EVENTS, 'userId', $userId);
    }
}

// Namespace bridge: allow App\Libraries\WechatEventsDao → App\Models\WechatEventsDao
class_alias('App\Models\WechatEventsDao', 'App\Libraries\WechatEventsDao');
