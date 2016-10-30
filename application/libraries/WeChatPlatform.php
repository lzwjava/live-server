<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 10/27/16
 * Time: 6:34 AM
 */
class WeChatPlatform
{
    /** @var SnsUserDao */
    var $snsUserDao = null;
    /** @var JSSDK */
    var $jsSdk = null;
    /** @var UserDao */
    var $userDao = null;

    function __construct()
    {
        $ci = get_instance();
        $ci->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $ci->load->library(JSSDK::class);
        $this->jsSdk = new JSSDK(WECHAT_APP_ID, WECHAT_APP_SECRET);
        $ci->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    function notifyUserByWeChat($userId, $live)
    {
        $user = $this->userDao->findUserById($userId);
        $tmplData = array(
            'first' => array(
                'value' => $user->username . '，您参与的直播即将开始啦',
                'color' => '#000',
            ),
            'keyword1' => array(
                'value' => $live->subject,
                'color' => '#173177',
            ),
            'keyword2' => array(
                'value' => $live->planTs,
                'color' => '#173177',
            ),
            'remark' => array(
                'value' => '点击进入直播，不见不散。',
                'color' => '#000',
            )
        );
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;
        return $this->notifyByWeChat($user, 'gKSNH1PPeKQqYC4yNPjXl-OrHNdoU1jkyv7468BM6R4', $url, $tmplData);
    }

    private function notifyByWeChat($user, $tempId, $url, $tmplData)
    {
        if (!$user->unionId) {
            logInfo("the user $user->username do not have unionId fail send wechat msg");
            return false;
        }
        $snsUser = $this->snsUserDao->getWechatSnsUser($user->unionId);
        $data = array(
            'touser' => $snsUser->openId,
            'template_id' => $tempId,
            'url' => $url,
            'topcolor' => '#FF0000',
            'data' => $tmplData
        );
        $accesstoken = $this->jsSdk->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $accesstoken;
        $res = $this->httpPost($url, $data);
        logInfo('notify wechat res ' . $res);
        if (!$res) {
            logInfo('wechat notified failed user:' . json_encode($user));
            return false;
        }
        $resp = json_decode($res);
        if ($resp->errcode != 0) {
            logInfo("wechat notified failed errcode != 0 user:" . json_encode($user));
            return false;
        }
        return true;
    }

    function notifyRefundByWeChat($userId, $live)
    {
        $user = $this->userDao->findUserById($userId);
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;
        $tmplData = array(
            'first' => array(
                'value' => $user->username . '，您好，由于正在进行优惠促销活动，退还部分金额。',
                'color' => '#000'
            ),
            'reason' => array(
                'value' => '优惠促销',
                'color' => '#173177'
            ),
            'refund' => array(
                'value' => '10元',
                'color' => '#173177',
            ),
            'remark' => array(
                'value' => '如有疑问,请致电或加微信 13261630925 联系我们。点击可进入直播, 晚上见。',
                'color' => '#000'
            )
        );
        return $this->notifyByWeChat($user, '122IpqfLsQaKMxHR0IVhiJN0YTqgEusoyJfFof-nrvk', $url, $tmplData);
    }

    private function httpPost($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

}
