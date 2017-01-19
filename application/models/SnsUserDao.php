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
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

    function bindUnionIdToSnsUser($openId, $platform, $unionId)
    {
        $sql = "UPDATE sns_users SET unionId=? WHERE openId=? AND platform=?";
        $binds = array($unionId, $openId, $platform);
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

    function getWechatSnsUser($unionId)
    {
        $sql = "SELECT * FROM sns_users WHERE unionId=? AND platform=?";
        $binds = array($unionId, PLATFORM_WECHAT);
        return $this->db->query($sql, $binds)->row();
    }

    function getWeChatSnsUserByOpenId($openId)
    {
        return $this->getSnsUser($openId, PLATFORM_WECHAT);
    }

    function getUserIdByOpenId($openId)
    {
        $snsUser = $this->getWeChatSnsUserByOpenId($openId);
        if (!$snsUser) {
            return null;
        } else {
            return $snsUser->userId;
        }
    }

    function getOpenIdByUserId($userId)
    {
        $snsUser = $this->getWeChatSnsUserByUserId($userId);
        if (!$snsUser) {
            return null;
        } else {
            return $snsUser->openId;
        }
    }

    function getWeChatSnsUserByUserId($userId)
    {
        $sql = "SELECT * FROM sns_users WHERE userId=? AND platform=?";
        $binds = array($userId, PLATFORM_WECHAT);
        return $this->db->query($sql, $binds)->row();
    }

    function getSnsUserByUser($user)
    {
        $snsUser = null;
        if ($user->unionId) {
            $snsUser = $this->getWechatSnsUser($user->unionId);
        }
        if (!$snsUser) {
            $snsUser = $this->getWeChatSnsUserByUserId($user->userId);
        }
        return $snsUser;
    }

    function getWxAppSnsUser($unionId)
    {
        $sql = "SELECT * FROM sns_users WHERE unionId=? AND platform=?";
        $binds = array($unionId, PLATFORM_WXAPP);
        return $this->db->query($sql, $binds)->row();
    }

}
