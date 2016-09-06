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
    }

    private function checkSmsCodeWrong($mobilePhoneNumber, $smsCode)
    {
        if (isDebug() && $smsCode == '5555') {
            // for test
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
        if ($this->checkIfParamsNotExist($_POST, array(KEY_MOBILE_PHONE_NUMBER))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST[KEY_MOBILE_PHONE_NUMBER];
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
        if ($this->checkIfParamsNotExist($_POST, array(KEY_USERNAME, KEY_MOBILE_PHONE_NUMBER,
            KEY_PASSWORD, KEY_SMS_CODE))
        ) {
            return;
        }
        $mobilePhoneNumber = $_POST[KEY_MOBILE_PHONE_NUMBER];
        $username = $_POST[KEY_USERNAME];
        $password = $_POST[KEY_PASSWORD];
        $smsCode = $_POST[KEY_SMS_CODE];
        if ($this->checkIfUsernameUsedAndReponse($username)) {
            return;
        } elseif ($this->userDao->isMobilePhoneNumberUsed($mobilePhoneNumber)) {
            $this->failure(ERROR_MOBILE_PHONE_NUMBER_TAKEN);
        } else if ($this->checkSmsCodeWrong($mobilePhoneNumber, $smsCode)) {
            return;
        } else if ($this->checkIfWrongPasswordFormat($password)) {
            return;
        } else {
            $defaultAvatarUrl = QINIU_FILE_HOST . "/defaultAvatar1.png";
            $this->userDao->insertUser($username, $mobilePhoneNumber, $defaultAvatarUrl,
                sha1($password));
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    private function genUsername($oldName)
    {
        $newUsername = $oldName;
        $time = 0;
        while ($this->userDao->isUsernameUsed($newUsername) && $time < 1000) {
            $newUsername = $oldName . random_string('alnum', 3);
            logInfo("newUsername: " . $newUsername);
            $time++;
        }
        if ($time < 1000) {
            return $newUsername;
        } else {
            return null;
        }
    }

    public function registerBySns_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_OPEN_ID, KEY_PLATFORM,
            KEY_MOBILE_PHONE_NUMBER, KEY_SMS_CODE))
        ) {
            return;
        }
        $mobile = $this->post(KEY_MOBILE_PHONE_NUMBER);
        $openId = $this->post(KEY_OPEN_ID);
        $platform = $this->post(KEY_PLATFORM);
        $smsCode = $this->post(KEY_SMS_CODE);
        if ($this->userDao->isMobilePhoneNumberUsed($mobile)) {
            $this->failure(ERROR_MOBILE_PHONE_NUMBER_TAKEN);
            return;
        }
        if ($this->checkSmsCodeWrong($mobile, $smsCode)) {
            return;
        }
        $snsUser = $this->snsUserDao->getSnsUser($openId, $platform);
        list($imageUrl, $error) = $this->qiniuDao->fetchImageAndUpload($snsUser->avatarUrl);
        if ($error) {
            $this->failure(ERROR_QINIU_UPLOAD);
            return;
        }
        $newUsername = $this->genUsername($snsUser->username);
        if ($newUsername == null) {
            $this->failure(ERROR_USERNAME_TAKEN);
            return;
        }
        $userId = $this->userDao->insertUser($newUsername, $mobile, $imageUrl, '');
        if (!$userId) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->loginOrRegisterSucceed($mobile);
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
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_MOBILE_PHONE_NUMBER, KEY_PASSWORD))) {
            return;
        }
        $mobilePhoneNumber = $this->post(KEY_MOBILE_PHONE_NUMBER);
        $password = $this->post(KEY_PASSWORD);
        if ($this->checkIfWrongPasswordFormat($password)) {
            return;
        } else if ($this->userDao->checkLogin($mobilePhoneNumber, $password) == false) {
            $this->failure(ERROR_LOGIN_FAILED);
        } else {
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    public function loginOrRegisterSucceed($mobilePhoneNumber)
    {
        $user = $this->userDao->setLoginByMobilePhone($mobilePhoneNumber);
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
        $keys = array(KEY_AVATAR_URL, KEY_USERNAME);
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
        $user = $this->userDao->updateUser($user, $data);
        $this->succeed($user);
    }

    public function isRegister_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_MOBILE_PHONE_NUMBER))) {
            return;
        }
        $mobile = $this->get(KEY_MOBILE_PHONE_NUMBER);
        $used = $this->userDao->isMobilePhoneNumberUsed($mobile);
        $this->succeed($used);
    }

}
