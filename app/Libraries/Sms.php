<?php

namespace App\Libraries;

use App\Models\UserDao;

/**
 * Sms - SMS notification service via LeanCloud
 * CI4-compatible version (no get_instance(), no $this->load, extends BaseDao behavior)
 */
class Sms
{
    public $leancloud;
    public $userDao;

    public function __construct()
    {
        $this->leancloud = new LeanCloud();
        $this->userDao = new UserDao();
    }

    public function notifyLiveStart(int $userId, $live, bool $oneHour = false): bool
    {
        $realUser = $this->userDao->findUserById($userId);
        if (!$realUser) {
            return false;
        }
        $name = $realUser->username ?? '';
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $lcMaxNameLen = defined('LC_MAX_NAME_LEN') ? LC_MAX_NAME_LEN : 10;
            if (mb_strlen($name) > $lcMaxNameLen) {
                $name = mb_substr($name, 0, $lcMaxNameLen);
            }
        }
        $extraWord = '';
        if ($oneHour) {
            $extraWord = '，还有1小时开始';
        }
        $smsName = defined('SMS_NAME') ? SMS_NAME : 'name';
        $keySubject = defined('KEY_SUBJECT') ? KEY_SUBJECT : 'subject';
        $smsOpenAppWords = defined('SMS_OPEN_APP_WORDS') ? SMS_OPEN_APP_WORDS : 'openAppWords';
        $data = [
            $smsName => $name,
            $keySubject => $live->subject,
            $smsOpenAppWords => '请关注公众号「平方根科技」或用电脑打开 quzhiboapp.com' . $extraWord,
        ];
        $phone = $realUser->mobilePhoneNumber;
        return $this->leancloud->sendTemplateSms($phone, 'LiveStart', $data);
    }

    public function notifyVideoReady(int $userId, $live): bool
    {
        $realUser = $this->userDao->findUserById($userId);
        if (!$realUser) {
            return false;
        }
        $name = $realUser->username ?? '';
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $lcMaxNameLen = defined('LC_MAX_NAME_LEN') ? LC_MAX_NAME_LEN : 10;
            if (mb_strlen($name) > $lcMaxNameLen) {
                $name = mb_substr($name, 0, $lcMaxNameLen);
            }
        }
        $smsName = defined('SMS_NAME') ? SMS_NAME : 'name';
        $keySubject = defined('KEY_SUBJECT') ? KEY_SUBJECT : 'subject';
        $keyRemark = defined('KEY_REMARK') ? KEY_REMARK : 'remark';
        $data = [
            $smsName => $name,
            $keySubject => $live->subject,
            $keyRemark => '，同时请关注服务号「平方根科技」来接受微信通知，因为价格高昂短信将不再通知，抱歉',
        ];
        $phone = $realUser->mobilePhoneNumber;
        return $this->leancloud->sendTemplateSms($phone, 'VideoReady', $data);
    }

    public function groupSend($thirdUser, $live): bool
    {
        $name = '';
        if (function_exists('mb_substr')) {
            $name = mb_substr($thirdUser->username ?? '', 0, 8);
        } else {
            $name = substr($thirdUser->username ?? '', 0, 8);
        }
        $smsName = defined('SMS_NAME') ? SMS_NAME : 'name';
        $smsIntro = defined('SMS_INTRO') ? SMS_INTRO : 'intro';
        $smsTime = defined('SMS_TIME') ? SMS_TIME : 'time';
        $smsOwnerName = defined('SMS_OWNER_NAME') ? SMS_OWNER_NAME : 'ownerName';
        $keySubject = defined('KEY_SUBJECT') ? KEY_SUBJECT : 'subject';
        $smsLink = defined('SMS_LINK') ? SMS_LINK : 'link';
        $data = [
            $smsName => $name,
            $smsIntro => '北林助手作者李智维，也是大四的学生',
            $smsTime => '今天晚上8点半',
            $smsOwnerName => '北林校友与创业者程成',
            $keySubject => '《' . $live->subject . '》',
            $smsLink => 'http://m.quzhiboapp.com/?liveId=' . $live->liveId . ' 感谢您。想和我交朋友的也可加微信哈。',
        ];
        return $this->leancloud->sendTemplateSms($thirdUser->mobilePhoneNumber, 'Invite', $data);
    }
}
