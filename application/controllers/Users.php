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

    function __construct()
    {
        parent::__construct();
        $this->load->library(LeanCloud::class);
        $this->leancloud = new LeanCloud();
    }

    private function checkSmsCodeWrong($mobilePhoneNumber, $smsCode)
    {
        if ($smsCode == '5555') {
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
        } elseif ($this->userDao->checkIfMobilePhoneNumberUsed($mobilePhoneNumber)) {
            $this->failure(ERROR_MOBILE_PHONE_NUMBER_TAKEN);
        } else if ($this->checkSmsCodeWrong($mobilePhoneNumber, $smsCode)) {
            return;
        } else if ($this->checkIfWrongPasswordFormat($password)) {
            return;
        } else {
            $defaultAvatarUrl = "http://7xotd0.com1.z0.glb.clouddn.com/android.png";
            $this->userDao->insertUser($username, $mobilePhoneNumber, $defaultAvatarUrl,
                $password);
            $this->loginOrRegisterSucceed($mobilePhoneNumber);
        }
    }

    private function checkIfUsernameUsedAndReponse($username)
    {
        if ($this->userDao->checkIfUsernameUsed($username)) {
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
        $user = $this->userDao->updateSessionTokenIfNeeded($mobilePhoneNumber);
        setCookieForever(KEY_COOKIE_TOKEN, $user->sessionToken);
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

}
