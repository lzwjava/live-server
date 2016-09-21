<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/17/16
 * Time: 2:59 PM
 */
class Sms extends BaseDao
{
    /** @var LeanCloud */
    public $leancloud;
    /** @var UserDao */
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->library(LeanCloud::class);
        $this->leancloud = new LeanCloud();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    function notifyLiveStart($userId, $live)
    {
        $realUser = $this->userDao->findUserById($userId);
        $name = $realUser->username;
        if (mb_strlen($name) > 8) {
            $name = mb_substr($name, 0, 8);
        }
        $data = array(
            SMS_NAME => $name,
            KEY_SUBJECT => $live->subject,
            SMS_OPEN_APP_WORDS => '请回到报名时的微信网页观看~~'
        );
        $phone = $realUser->mobilePhoneNumber;
        return $this->leancloud->sendTemplateSms($phone, 'LiveStart', $data);
    }

}
