<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午2:13
 */
class UserDao extends BaseDao
{
    private function isUserUsed($field, $value)
    {
        $sql = "SELECT * FROM users WHERE $field =?";
        $array[] = $value;
        return $this->db->query($sql, $array)->num_rows() > 0;
    }

    function isUsernameUsed($username)
    {
        return $this->isUserUsed(KEY_USERNAME, $username);
    }

    function isMobilePhoneNumberUsed($mobilePhoneNumber)
    {
        return $this->isUserUsed(KEY_MOBILE_PHONE_NUMBER, $mobilePhoneNumber);
    }

    function insertUser($username, $mobilePhoneNumber, $avatarUrl, $unionId = null, $subscribe = 0)
    {
        $data = array(
            KEY_USERNAME => $username,
            KEY_AVATAR_URL => $avatarUrl,
            KEY_SESSION_TOKEN => $this->genSessionToken(),
            KEY_UNION_ID => $unionId,
            KEY_WECHAT_SUBSCRIBE => $subscribe
        );
        if ($data) {
            $data[KEY_MOBILE_PHONE_NUMBER] = $mobilePhoneNumber;
        }
        $this->db->insert(TABLE_USERS, $data);
        return $this->db->insert_id();
    }

    private function genId()
    {
        return getToken(16);
    }

    private function genSessionToken()
    {
        return getToken(32);
    }

    function checkLogin($mobilePhoneNumber, $password)
    {
        $sql = "SELECT * FROM users WHERE mobilePhoneNumber=? AND password=?";
        $array[] = $mobilePhoneNumber;
        $array[] = sha1($password);
        return $this->db->query($sql, $array)->num_rows() == 1;
    }

    private function findUser($field, $value, $cleanFields = true)
    {
        $user = $this->findActualUser($field, $value);
        if ($user) {
            if ($cleanFields) {
                $this->cleanUserFieldsForAll($user);
            }
        }
        return $user;
    }

    private function sessionUserFields()
    {
        return $this->mergeFields(array(
            KEY_USER_ID,
            KEY_AVATAR_URL,
            KEY_USERNAME,
            KEY_MOBILE_PHONE_NUMBER,
            KEY_SESSION_TOKEN_CREATED,
            KEY_SESSION_TOKEN,
            KEY_UNION_ID,
            KEY_WECHAT_SUBSCRIBE,
            KEY_CREATED,
            KEY_UPDATED,
        ));
    }

    function findPublicUser($field, $value)
    {
        $fields = $this->userPublicFields();
        return $this->getOneFromTable(TABLE_USERS, $field, $value, $fields);
    }

    function findPublicUserById($id)
    {
        return $this->findPublicUser(KEY_USER_ID, $id);
    }

    function findUserByUnionId($unionId)
    {
        return $this->findUser(KEY_UNION_ID, $unionId);
    }

    private function findActualUser($field, $value)
    {
        $fields = $this->sessionUserFields();
        $user = $this->getOneFromTable(TABLE_USERS, $field, $value, $fields);
        return $user;
    }

    function findUserBySessionToken($sessionToken)
    {
        return $this->findUser(KEY_SESSION_TOKEN, $sessionToken);
    }

    function findUserById($id)
    {
        return $this->findUser(KEY_USER_ID, $id);
    }

    function findUserByMobile($mobile)
    {
        return $this->findUser(KEY_MOBILE_PHONE_NUMBER, $mobile);
    }

    private function updateSessionToken($user)
    {
        $token = $this->genSessionToken();
        $result = $this->updateUserAndGet($user, array(
            KEY_SESSION_TOKEN => $token,
            KEY_SESSION_TOKEN_CREATED => dateWithMs()
        ));
        if ($result) {
            $user->sessionToken = $token;
        }
    }

    private function setLoginByUser($user)
    {
        $newUser = $this->updateSessionTokenByUserIfNeeded($user);
        setCookieForever(KEY_COOKIE_TOKEN, $newUser->sessionToken);
        return $newUser;
    }

    function setLoginByUserId($userId)
    {
        $user = $this->findUser(KEY_USER_ID, $userId, false);
        return $this->setLoginByUser($user);
    }

    private function updateSessionTokenByUserIfNeeded($user)
    {
        $created = strtotime($user->sessionTokenCreated);
        $now = dateWithMs();
        $nowMillis = strtotime($now);
        $duration = $nowMillis - $created;
        if ($user->sessionToken == null || $user->sessionTokenCreated == null
            || $duration > 60 * 60 * 24 * 30
        ) {
            $this->updateSessionToken($user);
        }
        $this->cleanUserFieldsForAll($user);
        return $user;
    }

    function updateMobile($userId, $mobile)
    {
        $data = array(KEY_MOBILE_PHONE_NUMBER => $mobile);
        return $this->updateUser($userId, $data);
    }

    function updateUser($userId, $data)
    {
        $this->db->where(KEY_USER_ID, $userId);
        $this->db->update(TABLE_USERS, $data);
        return $this->db->affected_rows() > 0;
    }

    function updateUserAndGet($user, $data)
    {
        $this->updateUser($user->userId, $data);
        return $this->findUser(KEY_USER_ID, $user->userId);
    }

    function bindUnionIdToUser($userId, $unionId)
    {
        $sql = "UPDATE users SET unionId=? WHERE userId=?";
        $binds = array($unionId, $userId);
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

    private function cleanUserFieldsForAll($user)
    {
        if ($user) {
            unset($user->sessionTokenCreated);
            unset($user->password);
        }
    }

    private function cleanUserFieldsForPrivacy($user)
    {
        if ($user) {
            unset($user->sessionToken);
            unset($user->mobilePhoneNumber);
            unset($user->created);
            unset($user->type);
        }
    }

    function findWxlogoUsers()
    {
        $sql = "SELECT * FROM users WHERE avatarUrl LIKE 'http://wx.qlogo.cn%'";
        return $this->db->query($sql)->result();
    }

    function updateSubscribe($userId, $subscribe)
    {
        return $this->updateUser($userId, array(KEY_WECHAT_SUBSCRIBE => $subscribe));
    }

    function findAllUsers()
    {
        return $this->getListFromTable(TABLE_USERS, '1', '1',
            $this->sessionUserFields(), null, 0, ROW_MAX);
    }

    function count()
    {
        return $this->db->count_all(TABLE_USERS);
    }

}
