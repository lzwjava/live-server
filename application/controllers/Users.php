<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午2:37
 */

if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

class Users extends BaseController
{
    public $leancloud;
    public $snsUserDao;
    public $qiniuDao;
    public $jsSdk;
    public $weChatPlatform;
    public $liveDao;

    function __construct()
    {
        parent::__construct();
        $this->load->library(LeanCloud::class);
        $this->leancloud = new LeanCloud();
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $this->load->model(QiniuDao::class);
        $this->qiniuDao = new QiniuDao();
        $this->load->helper('string');
        $this->load->library(JSSDK::class);
        $this->jsSdk = new JSSDK();
        $this->load->library(WeChatPlatform::class);
        $this->weChatPlatform = new WeChatPlatform();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    private function checkSmsCodeWrong($mobilePhoneNumber, $smsCode)
    {
        if (isDebug() && $smsCode == '5555') {
            // for test
            return false;
        }
        if (in_array($mobilePhoneNumber, specialPhones())) {
            return false;
        }
        $return = $this->leancloud->curlLeanCloud("verifySmsCode/" . $smsCode . "?mobilePhoneNumber=" .
            $mobilePhoneNumber,
            null);
        if ($return['status'] == 200) {
            return false;
        } else {
            $this->failure(ERROR_SMS_WRONG, $return['result']);
            return true;
        }
    }

    public function requestSmsCode_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_MOBILE_PHONE_NUMBER))
        ) {
            return;
        }
        $mobilePhoneNumber = $this->post(KEY_MOBILE_PHONE_NUMBER);
        if (in_array($mobilePhoneNumber, specialPhones())) {
            $this->succeed();
            return;
        }
        $data = array(
            KEY_MOBILE_PHONE_NUMBER => $mobilePhoneNumber
        );
        $return = $this->leancloud->curlLeanCloud('requestSmsCode', $data);
        if ($return['status'] == 200) {
            $this->succeed();
        } else {
            $this->failure(ERROR_SMS_WRONG, $return['result']);
        }
    }

    private function checkIfWrongPasswordFormat($password)
    {
        if (strlen($password) != 32) {
            $this->failure(ERROR_PASSWORD_FORMAT, "密码未加密,不符合规范");
            return true;
        }
        return false;
    }

    public function register_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_USERNAME,
            KEY_MOBILE_PHONE_NUMBER, KEY_SMS_CODE, KEY_AVATAR_URL))
        ) {
            return;
        }
        $mobilePhoneNumber = $this->post(KEY_MOBILE_PHONE_NUMBER);
        $username = $this->post(KEY_USERNAME);
        $smsCode = $this->post(KEY_SMS_CODE);
        $avatarUrl = $this->post(KEY_AVATAR_URL);
        if ($this->checkIfUsernameUsedAndReponse($username)) {
            return;
        } elseif ($this->userDao->isMobilePhoneNumberUsed($mobilePhoneNumber)) {
            logInfo("mobilePhone is used: " . $mobilePhoneNumber);
            $this->failure(ERROR_MOBILE_PHONE_NUMBER_TAKEN);
            return;
        } else if ($this->checkSmsCodeWrong($mobilePhoneNumber, $smsCode)) {
            return;
        } else {
            $userId = $this->userDao->insertUser($username, $mobilePhoneNumber, $avatarUrl);
            if (!$userId) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
            $this->loginOrRegisterSucceed($userId);
        }
    }

    public function registerBySns_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_OPEN_ID, KEY_PLATFORM))
        ) {
            return;
        }
        $openId = $this->post(KEY_OPEN_ID);
        $platform = $this->post(KEY_PLATFORM);
        list($error, $userId) = $this->userDao->createUserByOpenId($openId, $platform);
        if ($error) {
            $this->failure($error);
            return;
        }
        $this->loginOrRegisterSucceed($userId);
    }


    function bindPhone_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_MOBILE_PHONE_NUMBER,
            KEY_SMS_CODE))
        ) {
            return;
        }
        $mobile = $this->post(KEY_MOBILE_PHONE_NUMBER);
        $smsCode = $this->post(KEY_SMS_CODE);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($user->mobilePhoneNumber) {
            $this->failure(ERROR_ALREADY_BIND_PHONE);
            return;
        }
        if ($this->userDao->isMobilePhoneNumberUsed($mobile)) {
            $this->failure(ERROR_MOBILE_PHONE_NUMBER_TAKEN);
            return;
        }
        if ($this->checkSmsCodeWrong($mobile, $smsCode)) {
            return;
        }
        $ok = $this->userDao->updateMobile($user->userId, $mobile);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

    private function checkIfUsernameUsedAndReponse($username)
    {
        if ($this->userDao->isUsernameUsed($username)) {
            $this->failure(ERROR_USERNAME_TAKEN);
            return true;
        } else {
            return false;
        }
    }

    public function login_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_MOBILE_PHONE_NUMBER,
            KEY_SMS_CODE))
        ) {
            return;
        }
        $mobilePhoneNumber = $this->post(KEY_MOBILE_PHONE_NUMBER);
        $smsCode = $this->post(KEY_SMS_CODE);
        if ($this->checkSmsCodeWrong($mobilePhoneNumber, $smsCode)) {
            return;
        }
        $user = $this->userDao->findUserByMobile($mobilePhoneNumber);
        $this->loginOrRegisterSucceed($user->userId);
    }

    public function loginOrRegisterSucceed($userId)
    {
        $user = $this->userDao->setLoginByUserId($userId);
        $this->succeed($user);
    }

    public function self_get()
    {
        $user = $this->getSessionUser();
        if ($user == null) {
            // $login_url = 'Location: /';
            // header($login_url);
            $this->failure(ERROR_NOT_IN_SESSION);
        } else {
            $this->succeed($user);
        }
    }

    public function logout_get()
    {
        session_unset(KEY_COOKIE_TOKEN);
        deleteCookie(KEY_COOKIE_TOKEN);
        $this->succeed();
    }

    public function update_post()
    {
        $keys = array(KEY_AVATAR_URL, KEY_USERNAME, KEY_LIVE_SUBSCRIBE, KEY_INCOME_SUBSCRIBE);
        if ($this->checkIfNotAtLeastOneParam($this->post(), $keys)
        ) {
            return;
        }
        $data = $this->postParams($keys);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if (isset($data[KEY_USERNAME])) {
            $username = $data[KEY_USERNAME];
            if ($username != $user->username) {
                if ($this->checkIfUsernameUsedAndReponse($username)) {
                    return;
                }
            }
        }
        if (isset($data[KEY_LIVE_SUBSCRIBE])) {
            $liveSubscribe = intval($data[KEY_LIVE_SUBSCRIBE]);
            if ($liveSubscribe != 0 && $liveSubscribe != 1) {
                $this->failure(ERROR_PARAMETER_ILLEGAL);
                return;
            }
            if ($liveSubscribe == 1 && $user->wechatSubscribe == 0) {
                $this->failure(ERROR_MUST_SUBSCRIBE);
                return;
            }
            if ($liveSubscribe == 1) {
                $live = $this->liveDao->findLatestWaitLive();
                if ($live) {
                    $this->weChatPlatform->notifyNewLive($user->userId, $live);
                }
            }
        }
        $user = $this->userDao->updateUserAndGet($user, $data);
        $this->succeed($user);
    }

    function isRegister_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_MOBILE_PHONE_NUMBER))) {
            return;
        }
        $mobile = $this->get(KEY_MOBILE_PHONE_NUMBER);
//
//        $isSpecial = in_array($mobile, array('18928980893'));
//        if (!$isSpecial) {
//            if (!isDebug()) {
//                $this->failure(ERROR_NOT_ALLOW_APP_LOGIN);
//                return;
//            }
//        }

        $used = $this->userDao->isMobilePhoneNumberUsed($mobile);
        $this->succeed($used);
    }

    function one_get($userId)
    {
        $user = $this->userDao->findPublicUserById($userId);
        if ($this->checkIfObjectNotExists($user)) {
            return;
        }
        $this->succeed($user);
    }

    function list_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_USER_IDS))) {
            return;
        }
        $userIds = $this->post(KEY_USER_IDS);
        $userIdArray = json_decode($userIds);
        $users = $this->userDao->findPublicUsers($userIdArray);
        $this->succeed($users);
    }

    function fixAvatarUrl_get()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $users = $this->userDao->findWxlogoUsers();
        $succeedCount = 0;
        foreach ($users as $user) {
            list($imageUrl, $imageKey, $error) = $this->qiniuDao->fetchImageAndUpload($user->avatarUrl);
            if ($error) {
                continue;
            }
            $ok = $this->userDao->updateUser($user->userId, array(KEY_AVATAR_URL => $imageUrl));
            if ($ok) {
                $succeedCount++;
            }
        }
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => count($users)));
    }

    function fixSystemId_get()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->trans_begin();
        $sql = "UPDATE users SET userId=100000 WHERE userId=0";
        $this->db->query($sql);
        $updated = $this->db->affected_rows() > 0;
        if (!$updated) {
            $this->db->trans_rollback();
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $transSql = "UPDATE transactions SET userId=100000 WHERE userId=0";
        $this->db->query($transSql);
        $transUpdated = $this->db->affected_rows() > 0;
        if (!$transUpdated) {
            $this->db->trans_rollback();
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $accountSql = "UPDATE accounts SET userId=100000 WHERE userId=0";
        $this->db->query($accountSql);
        $accountUpdated = $this->db->affected_rows() > 0;
        if (!$accountUpdated) {
            $this->db->trans_rollback();
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->db->trans_commit();
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        $this->succeed();
    }

    function userTopic_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_USERNAME))) {
            return;
        }
        $username = $this->get(KEY_USERNAME);
        $userId = $this->snsUserDao->getUserIdByUsername($username);
        if (!$userId) {
            $this->failure(ERROR_SNS_USER_NOT_EXISTS);
            return;
        }
        $user = $this->userDao->findUserById($userId);
        $lives = $this->liveDao->getAttendedLivesOfUser($user->userId, null);
        if (count($lives) <= 0) {
            $this->failure(ERROR_NOT_ATTEND_LIVE);
            return;
        }
        $live = $lives[0];
        $topic = $live->topic;
        if (!$topic) {
            $this->failure(ERROR_NOT_LIVE_TOPIC);
            return;
        }
        $this->succeed($topic);
    }

    public function usersByUsername_get()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_USERNAME))) {
            return;
        }
        $users = $this->userDao->findUsersByUsername($this->get(KEY_USERNAME));
        $this->succeed($users);
    }
}
