<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/6/16
 * Time: 2:51 AM
 */
class SnsUserDao extends BaseDao
{
    function addSnsUser($openId, $username, $avatarUrl, $platform, $unionId, $userId = 0)
    {
        $data = array(
            KEY_OPEN_ID => $openId,
            KEY_USERNAME => $username,
            KEY_AVATAR_URL => $avatarUrl,
            KEY_PLATFORM => $platform,
            KEY_UNION_ID => $unionId,
            KEY_USER_ID => $userId
        );
        $this->db->insert(TABLE_SNS_USERS, $data);
        return $this->db->insert_id();
    }

    function getSnsUser($openId, $platform)
    {
        $row = $this->db->get_where(TABLE_SNS_USERS, array(
            KEY_OPEN_ID => $openId,
            KEY_PLATFORM => $platform
        ))->row();
        return $row;
    }

    function getSnsUserByUnionId($unionId)
    {
        return $this->getOneFromTable(TABLE_SNS_USERS, KEY_UNION_ID, $unionId);
    }

    function bindUser($openId, $platform, $userId)
    {
        $sql = "UPDATE sns_users SET userId=? WHERE openId=? AND platform=?";
        $binds = array($userId, $openId, $platform);
        return $this->db->query($sql, $binds);
    }

    function bindUnionIdToSnsUser($openId, $platform, $unionId)
    {
        $sql = "UPDATE sns_users SET unionId=? WHERE openId=? AND platform=?";
        $binds = array($unionId, $openId, $platform);
        return $this->db->query($sql, $binds);
    }

    function getSnsUserByUserId($userId)
    {
        return $this->getOneFromTable(TABLE_SNS_USERS, KEY_USER_ID, $userId);
    }

}
