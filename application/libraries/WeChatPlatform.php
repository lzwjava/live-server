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
    /** @var LiveDao */
    var $liveDao = null;

    function __construct()
    {
        $ci = get_instance();
        $ci->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $ci->load->library(JSSDK::class);
        $this->jsSdk = new JSSDK(WECHAT_APP_ID, WECHAT_APP_SECRET);
        $ci->load->model(UserDao::class);
        $this->userDao = new UserDao();
        $ci->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $ci->load->helper('date');
    }

    function notifyUserByWeChat($userId, $live)
    {    // 活动即将开始通知
        $user = $this->userDao->findUserById($userId);
        $word = null;
        $a = date_create($live->planTs, new DateTimeZone('Asia/Shanghai'));
        $b = date_create('now');
        $diff = date_diff($a, $b);
        $hourStr = null;
        $word = null;
        if ($diff->h > 0) {
            $hourStr = $diff->format('%h小时%i分钟');
            $word = sprintf('，您参与的直播还有%s开始', $hourStr);
        } else if ($diff->i > 0) {
            $hourStr = $diff->format('%i分钟');
            $word = sprintf('，您参与的直播还有%s开始', $hourStr);
        } else if ($diff->invert < 0) {
            $word = '，您参与的直播已经开始';
        } else {
            $word = '，您参与的直播即将开始';
        }
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;

        $tmplData = array(
            'first' => array(
                'value' => $user->username . $word,
                'color' => '#D00019',
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

        $customMsgData = array(
            'msgtype' => 'news',
            'news' => array(
                'articles' => array(
                    array(
                        'title' => $user->username . $word,
                        'description' => "您已经报名了直播:『{$live->subject}』\n\n开播时间: {$live->planTs} \n\n点击进入直播",
                        'url' => $url,
                        'picurl' => $live->coverUrl
                    ),
                )
            )
        );
        if ($this->notifyLiveByWeChatCustom($user, $customMsgData)) {
            return true;
        } else {
            return $this->notifyByWeChat($user, 'gKSNH1PPeKQqYC4yNPjXl-OrHNdoU1jkyv7468BM6R4', $url, $tmplData);
        }
    }

    /**
     * 在微信平台上 以多篇文章的形式 向用户发送直播信息
     * @param $userId
     * @param $resultLives
     * @return bool
     */
    function sendLivesByWechat($userId, $lives)

    {
        $user = $this->userDao->findUserById($userId);

        if (empty($lives)){
            return false;
        }

        $liveArticlesArray = array();
        foreach ($lives as $value) {
            array_push($liveArticlesArray,array(
                "title" => $value->subject,
                "description" => '直播描述',
                "url" => 'http://m.quzhiboapp.com/?liveId=' . $value->liveId,
                "picurl" => $value->coverUrl
            ));
        }

        $customMsgData = array(
            'msgtype' => 'news',
            'news' => array('articles' => $liveArticlesArray)
        );

        if ($this->notifyLiveByWeChatCustom($user, $customMsgData)) {
            return true;
        }
    }

    function notifyNewLive($userId, $live)
    {
        $user = $this->userDao->findUserById($userId);
        $word = null;
        $tmplData = array(
            'first' => array(
                'value' => $user->username . '，有新的直播发布啦',
                'color' => '#828282',
            ),
            'keyword1' => array(
                'value' => $live->subject,
                'color' => '#00A2C0',
            ),
            'keyword2' => array(
                'value' => '趣直播',
                'color' => '#828282',
            ),
            'keyword3' => array(
                'value' => $live->owner->username,
                'color' => '#00A2C0',
            ),
            'keyword4' => array(
                'value' => $live->planTs,
                'color' => '#828282',
            ),
            'remark' => array(
                'value' => '点击查看详情，回复TD0000退订新直播发布',
                'color' => '#828282',
            )
        );
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;
        return $this->notifyByWeChat($user, 'w2ScyoZiWxhr3j_aSrSR8McrQpIwwKvDiaMYce__NdU', $url, $tmplData);
    }

    private function notifyByWeChat($user, $tempId, $url, $tmplData)
    {
        if (!$user->unionId) {
            logInfo("the user $user->userId do not have unionId fail send wechat msg");
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
        list($error, $res) = $this->jsSdk->wechatHttpPost('message/template/send', $data);
        if (!is_null($error)) {
            logInfo("wechat notified failed user: $user->userId name: $user->username error: . $error");
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

    function notifyWithdraw($withdraw, $systemAuto = false)
    {
        $user = $this->userDao->findUserById($withdraw->userId);
        $first = null;
        if ($systemAuto) {
            $first = '感谢您邀请朋友来参加或直播获得收益，系统自动每日提现给您';
        } else {
            $first = '您的提现申请已处理';
        }
        $tmplData = array(
            'first' => array(
                'value' => $first,
                'color' => '#D00019'
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
                'value' => '已给您企业转账,请查收',
                'color' => '#00A2C0'
            )
        );
        return $this->notifyByWeChat($user, 'lNmzB_7IA36S_4uy0NccBexKkrTy4rybAJhAlmcSPpw', null, $tmplData);
    }

    function notifyNewWithdraw($withdraw)
    {
        if (isDebug()) {
            $user = $this->userDao->findUserById($withdraw->userId);
        } else {
            $user = $this->userDao->findUserById(ADMIN_OP_USER_ID);
        }
        $withdrawUser = $this->userDao->findUserById($withdraw->userId);
        $tmplData = array(
            'first' => array(
                'value' => '有新的提现申请',
                'color' => '#000'
            ),
            'keyword1' => array(
                'value' => $withdrawUser->username,
                'color' => '#000'
            ),
            'keyword2' => array(
                'value' => date('Y-m-d H:i'),
                'color' => '#000'
            ),
            'keyword3' => array(
                'value' => moneyFormat($withdraw->amount) . '元',
                'color' => '#00A2C0'
            ),
            'keyword4' => array(
                'value' => '微信账户',
                'color' => '#000'
            ),
            'remark' => array(
                'value' => '提现ID为:' . $withdraw->withdrawId . ',请尽快处理',
                'color' => '#00A2C0'
            )
        );
        return $this->notifyByWeChat($user, 'fD-twBRM96P4FkBSZQHPJ4GJ8_1i9N7HaDe7A36CllY', null, $tmplData);
    }

    function notifyNewReview($live)
    {
        if (isDebug()) {
            $targetUser = $this->userDao->findUserById($live->ownerId);
        } else {
            $targetUser = $this->userDao->findUserById(ADMIN_OP_USER_ID);
        }
        $liveOwner = $this->userDao->findUserById($live->ownerId);
        $tmplData = array(
            'first' => array(
                'value' => $liveOwner->username . '提交了直播, 等待审核',
                'color' => '#000'
            ),
            'keyword1' => array(
                'value' => $live->subject,
                'color' => '#000'
            ),
            'keyword2' => array(
                'value' => '等待审核',
                'color' => '#000'
            ),
            'remark' => array(
                'value' => '直播ID为:' . $live->liveId . ', 请尽快处理',
                'color' => '#00A2C0'
            )
        );
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;
        return $this->notifyByWeChat($targetUser, 'NN03-O5T23mawo9yado4EV-ycEM-I2-Q92VwLd4pifM',
            $url, $tmplData);
    }

    /**
     * @param int $nightHour
     * @param int $morningHour
     * @return bool
     */
    private function inDisturbHour($nightHour = 23, $morningHour = 9)
    {
        $hour = intval(
            (new DateTime(null, new DateTimeZone('Asia/Shanghai')))->format('H')
        );
        return ($hour >= $nightHour) || ($hour < $morningHour);
    }

    function notifyNewIncome($incomeType, $amount, $live,
                             $fromUser, $inviteFromUserId = null)
    {
        if ($this->inDisturbHour()) {
            logInfo("notifyNewIncome inDisturbHour $incomeType, $amount, $live->ownerId, $fromUser->userId");
            return false;
        }
        $attendUser = $this->userDao->findUserById($fromUser->userId);
        $liveOwner = $this->userDao->findUserById($live->ownerId);
        $word = null;
        $incomeTypeWord = null;
        $actionWord = null;
        $notifyUser = null;
        if ($incomeType == TRANS_TYPE_LIVE_INCOME) {
            $incomeTypeWord = '赞助直播';
            $actionWord = sprintf('%s赞助了您的直播《%s》', $attendUser->username,
                $live->subject);
            $notifyUser = $liveOwner;
        } else if ($incomeType == TRANS_TYPE_REWARD_INCOME) {
            $incomeTypeWord = '打赏直播';
            $actionWord = sprintf('%s打赏了您的直播《%s》', $attendUser->username,
                $live->subject);
            $notifyUser = $liveOwner;
        } else if ($incomeType == TRANS_TYPE_INVITE_INCOME) {
            $inviteFromUser = $this->userDao->findUserById($inviteFromUserId);
            $incomeTypeWord = '邀请参加直播';
            $actionWord = sprintf('%s参加了您分享的直播《%s》', $attendUser->username,
                $live->subject);
            $notifyUser = $inviteFromUser;
        }
        if (!$notifyUser->incomeSubscribe) {
            logInfo("$notifyUser->userId unsubscribe income");
            return false;
        }
        $tmplData = array(
            'first' => array(
                'value' => sprintf('%s，您获得%s元收益', $actionWord, moneyFormat($amount)),
                'color' => '#D00019'
            ),
            'keyword1' => array(
                'value' => $incomeTypeWord,
                'color' => '#000'
            ),
            'keyword2' => array(
                'value' => date('Y-m-d H:i'),
                'color' => '#000'
            ),
            'remark' => array(
                'value' => '您的努力初见成效，再接再励哟。回复TD0001退订收益提醒',
                'color' => '#000'
            )
        );
        $url = 'http://m.quzhiboapp.com/#account';
        return $this->notifyByWeChat($notifyUser, 'Clt9LxKunzjbXwMOYAOoB6w_-40u9FUdv7Men4vTluc',
            $url, $tmplData);
    }

    private function notifyLiveByWeChatCustom($user, $data)
    {
        /* 发客服消息给用户提醒直播开始，如果不成功再使用模板消息
         * https://mp.weixin.qq.com/wiki?id=mp1421140547&highline=%E6%B6%88%E6%81%AF%7C%26%E5%9B%BE%E6%96%87%E6%B6%88%E6%81%AF%7C%26%E5%9B%BE%E6%96%87
         */
        if (!$user->unionId) {
            logInfo("the user $user->userId do not have unionId fail send wechat msg");
            return false;
        }
        $snsUser = $this->snsUserDao->getWechatSnsUser($user->unionId);
        $data['touser'] = $snsUser->openId;
        list($error, $res) = $this->jsSdk->wechatHttpPost('message/custom/send', $data);
        if (!is_null($error)) {
            logInfo("wechat custom notified failed errcode != 0 user: $user->userId
                 name:  $user->username code: $res error: $error");
            return false;
        }
        return true;
    }

    /**
     * 用户报名成功 通过微信模板消息提醒用户
     * @param int  $userId  用户ID
     * @param object  $live    直播ID
     * @return bool  是否推送成功
     */
    public function notifyUserAttendanceSuccessByWeChat($userId, $live)
    {
        $user = $this->userDao->findUserById($userId);
        $hourStr = null;
        $word = '，您参与的直播已经报名成功';

        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;

        $tmplData = array(
            'first' => array(
                'value' => $user->username . $word,
                'color' => '#D00019',
            ),
            'keyword1' => array(
                'value' => $user->username,
                'color' => '#173177',
            ),
            'keyword2' => array(
                'value' => $live->subject,
                'color' => '#173177',
            ),
            'keyword3' => array(
                'value' => $live->planTs,
                'color' => '#173177',
            ),
            'keyword4' => array(
                'value' => $live->liveId,
                'color' => '#173177',
            ),
            'remark' => array(
                'value' => '点击进入直播，不见不散。',
                'color' => '#000',
            )
        );

        $customMsgData = array(
            'msgtype' => 'news',
            'news' => array(
                'articles' => array(
                    array(
                        'title' => $user->username . $word,
                        'description' => "您已经成功报名直播:『{$live->subject}』\n\n开播时间: {$live->planTs} \n\n点击进入直播",
                        'url' => $url,
                        'picurl' => $live->coverUrl
                    ),
                )
            )
        );

        if ($this->notifyLiveByWeChatCustom($user, $customMsgData)) {
            return true;
        } else {
            return $this->notifyByWeChat($user, 'o6Mtcjn4w2aQ7DqwyQdFcwzC57Sr8-O1sK-5s_kuG9Q', $url, $tmplData);
        }

    }
}
