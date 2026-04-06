<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 4/7/17
 * Time: 3:38 AM
 */
class WechatGroupDao extends BaseDao
{
    protected $table = 'wechat_group';

    function createGroup($groupUserName, $qrcodeKey, $topicId)
    {
        $data = array(
            KEY_GROUP_USER_NAME => $groupUserName,
            KEY_QRCODE_KEY => $qrcodeKey,
            KEY_TOPIC_ID => $topicId
        );
        $this->db->table(TABLE_WECHAT_GROUPS)->insert($data);
        return $this->db->insertID();
    }

    function currentGroup($topicId)
    {
        $sql = "SELECT * FROM wechat_groups WHERE used = 0 AND topicId=? ORDER BY created ASC LIMIT 1";
        $binds = array($topicId);
        $group = $this->db->query($sql, $binds)->getRow();
        if ($group) {
            $this->assembleGroup($group);
        }
        return $group;
    }

    function queryGroup($groupUserName)
    {
        return $this->getOneFromTable(TABLE_WECHAT_GROUPS, KEY_GROUP_USER_NAME, $groupUserName);
    }

    function allGroups()
    {
        return $this->getListFromTable(TABLE_WECHAT_GROUPS, '1', '1');
    }

    private function updateRow($groupUserName, $data): bool
    {
        return $this->db->table(TABLE_WECHAT_GROUPS)->where(KEY_GROUP_USER_NAME, $groupUserName)->update($data) !== false;
    }

    function setUsed($groupUsername)
    {
        return $this->update($groupUsername, array(
            KEY_USED => 1
        ));
    }

    function updateMemberCount($groupUserName, $memberCount)
    {
        $data = array(
            KEY_MEMBER_COUNT => $memberCount
        );
        return $this->update($groupUserName, $data);
    }

    private function assembleGroup($group)
    {
        $group->qrcodeUrl = QINIU_FILE_HOST . '/' . $group->qrcodeKey;
    }
}

// Namespace bridge: allow App\Libraries\WechatGroupDao → App\Models\WechatGroupDao
class_alias('App\Models\WechatGroupDao', 'App\Libraries\WechatGroupDao');
