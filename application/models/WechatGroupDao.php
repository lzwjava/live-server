<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 4/7/17
 * Time: 3:38 AM
 */
class WechatGroupDao extends BaseDao
{
    function createGroup($groupUserName, $qrcodeKey, $topicId)
    {
        $data = array(
            KEY_GROUP_USER_NAME => $groupUserName,
            KEY_QRCODE_KEY => $qrcodeKey,
            KEY_TOPIC_ID => $topicId
        );
        $this->db->insert(TABLE_WECHAT_GROUPS, $data);
        return $this->db->insert_id();
    }

    function currentGroup($topicId)
    {
        $sql = "SELECT * FROM wechat_groups WHERE used = 0 AND topicId=? ORDER BY created ASC LIMIT 1";
        $binds = array($topicId);
        $group = $this->db->query($sql, $binds)->row();
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

    private function update($groupUserName, $data)
    {
        $this->db->where(KEY_GROUP_USER_NAME, $groupUserName);
        $this->db->update(TABLE_WECHAT_GROUPS, $data);
        return $this->db->affected_rows() > 0;
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
