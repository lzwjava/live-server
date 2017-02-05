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

    function notifyUserByWeChat($userId, $live, $oneHour = false)
    {
        $user = $this->userDao->findUserById($userId);
        $word = null;
        if ($oneHour) {
            $word = '，您参与的直播还有一个小时开始，请准备好小凳子哟';
        } else {
            $word = '，您参与的直播即将开始啦';
        }
        $tmplData = array(
            'first' => array(
                'value' => $user->username . $word,
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
            'topcolor' => '#FF0000',
            'data' => $tmplData
        );
        if ($url) {
            $data['url'] = $url;
        }
        $accesstoken = $this->jsSdk->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $accesstoken;
        $res = $this->httpPost($url, $data);
        if (!$res) {
            logInfo('wechat notified failed user:' . json_encode($user));
            return false;
        }
        $resp = json_decode($res);
        if ($resp->errcode != 0) {
            logInfo("wechat notified failed errcode != 0 user:" . $user->userId
                . ' name: ' . $user->username . ' res ' . $res);
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
                'value' => $user->username . '，您好，由于技术原因, 现退款第二天报名费给你',
                'color' => '#000'
            ),
            'reason' => array(
                'value' => '两天直播归为一天',
                'color' => '#173177'
            ),
            'refund' => array(
                'value' => '59元',
                'color' => '#173177',
            ),
            'remark' => array(
                'value' => '如有疑问,请致电或加微信 13261630925 联系我们。点击可进入直播, 晚上见。',
                'color' => '#000'
            )
        );
        return $this->notifyByWeChat($user, '122IpqfLsQaKMxHR0IVhiJN0YTqgEusoyJfFof-nrvk', $url, $tmplData);
    }

    function notifyVideoByWeChat($userId, $live)
    {
        $user = $this->userDao->findUserById($userId);
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;
        $tmplData = array(
            'first' => array(
                'value' => $user->username . '，您参与的直播已经可以收看回放。',
                'color' => '#000'
            ),
            'keyword1' => array(
                'value' => $live->owner->username,
                'color' => '#000'
            ),
            'keyword2' => array(
                'value' => $live->subject,
                'color' => '#173177'
            ),
            'keyword3' => array(
                'value' => $live->planTs,
                'color' => '#000',
            ),
            'remark' => array(
                'value' => '详情请点击。',
                'color' => '#000'
            )
        );
        return $this->notifyByWeChat($user, '_uG1HsFgQABk9_gK502OIaTuPEcHUAUEfRlR1cyfVFE', $url, $tmplData);
    }

    function notifyReviewByWeChat($application)
    {
        $user = $this->userDao->findUserById($application->userId);
        $firstWord = null;
        $remark = null;
        $statusWord = null;
        if ($application->status == APPLICATION_STATUS_SUCCEED) {
            $firstWord = $user->username . '，恭喜您成为趣直播的主讲人。';
            $remark = '我们将尽快联系和协助您创建直播';
            $statusWord = '已通过';
        } else if ($application->status == APPLICATION_STATUS_REJECT) {
            $firstWord = $user->username . '，很抱歉，未能通过您的主播申请。';
            $remark = '原因: ' . $application->reviewRemark;
            $statusWord = '未通过';
        }
        $url = 'http://m.quzhiboapp.com/#apply';
        $tmplData = array(
            'first' => array(
                'value' => $firstWord,
                'color' => '#000'
            ),
            'keyword1' => array(
                'value' => '无',
                'color' => '#000'
            ),
            'keyword2' => array(
                'value' => $statusWord,
                'color' => '#173177'
            ),
            'keyword3' => array(
                'value' => date('Y-m-d'),
                'color' => '#000',
            ),
            'remark' => array(
                'value' => $remark,
                'color' => '#000'
            )
        );
        return $this->notifyByWeChat($user, '96n9JfS5RcMraNsZcM_kQu7wyXede2gB7h77El386hM', $url, $tmplData);
    }

    function notifyOwnerByUserPacket($grabUserId, $ownerUserId, $packetId,
                                     $amount, $wishing, $toOwner = true)
    {
        $user = $this->userDao->findUserById($grabUserId);
        $owner = $this->userDao->findUserById($ownerUserId);
        $result = null;
        if ($toOwner) {
            $result = $user->username . '抢到了您的红包';
        } else {
            $result = $user->username . '，您抢到了' . $owner->username . '的红包';
        }
        $username = null;
        if ($toOwner) {
            $username = $user->username;
        } else {
            $username = $owner->username;
        }
        $url = 'http://m.quzhiboapp.com/?type=packet&packetId=' . $packetId;
        $remark = $wishing;
        $tmplData = array(
            'first' => array(
                'value' => $result,
                'color' => '#000'
            ),
            'keyword1' => array(
                'value' => floor($amount / 100.0) . '元',
                'color' => '#173177'
            ),
            'keyword2' => array(
                'value' => $username,
                'color' => '#173177'
            ),
            'remark' => array(
                'value' => $remark,
                'color' => '#000'
            )
        );
        return $this->notifyByWeChat($owner, 'H7LOlSlgG1O8ohoese6k4_kqwjarXAdsOgbn0x8vTQU', $url, $tmplData);
    }

    function notifyWithdraw($withdraw)
    {
        $user = $this->userDao->findUserById($withdraw->userId);
        $tmplData = array(
            'first' => array(
                'value' => '您的提现申请已处理',
                'color' => '#000'
            ),
            'keyword1' => array(
                'value' => $user->username,
                'color' => '#000'
            ),
            'keyword2' => array(
                'value' => moneyFormat($withdraw->amount) . '元',
                'color' => '#00A2C0'
            ),
            'keyword3' => array(
                'value' => '微信账户',
                'color' => '#000'
            ),
            'keyword4' => array(
                'value' => date('Y-m-d H:i'),
                'color' => '#000'
            ),
            'keyword5' => array(
                'value' => '成功',
                'color' => '#000'
            ),
            'remark' => array(
                'value' => '提现成功，已转账到您的微信，请查收',
                'color' => '#00A2C0'
            )
        );
        return $this->notifyByWeChat($user, 'lNmzB_7IA36S_4uy0NccBexKkrTy4rybAJhAlmcSPpw', null, $tmplData);
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
