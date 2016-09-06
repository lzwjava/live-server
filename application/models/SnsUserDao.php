<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/6/16
 * Time: 2:51 AM
 */
class SnsUserDao extends BaseDao
{
    function addSnsUser($openId, $username, $avatarUrl, $platform)
    {
        $data = array(
            KEY_OPEN_ID => $openId,
            KEY_USERNAME => $username,
            KEY_AVATAR_URL => $avatarUrl,
            KEY_PLATFORM => $platform
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

    function bindUser($openId, $platform, $userId)
    {
        $sql = "UPDATE sns_users SET userId=? WHERE openId=? AND platform=?";
        $binds = array($userId, $openId, $platform);
        return $this->db->query($sql, $binds);
    }
    
}
