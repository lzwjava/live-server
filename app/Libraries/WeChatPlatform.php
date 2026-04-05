<?php

namespace App\Libraries;

use App\Models\SnsUserDao;
use App\Models\UserDao;
use App\Models\LiveDao;

/**
 * WeChatPlatform - WeChat notification/Template message service
 * CI4-compatible version (no get_instance(), no $this->load)
 */
class WeChatPlatform
{
    private ?SnsUserDao $snsUserDao = null;
    private ?JSSDK $jsSdk = null;
    private ?UserDao $userDao = null;
    private ?LiveDao $liveDao = null;

    public function __construct()
    {
    }

    private function getSnsUserDao(): SnsUserDao
    {
        if ($this->snsUserDao === null) {
            $this->snsUserDao = new SnsUserDao();
        }
        return $this->snsUserDao;
    }

    private function getJsSdk(): JSSDK
    {
        if ($this->jsSdk === null) {
            $this->jsSdk = new JSSDK(env('WECHAT_APP_ID'), env('WECHAT_APP_SECRET'));
        }
        return $this->jsSdk;
    }

    private function getUserDao(): UserDao
    {
        if ($this->userDao === null) {
            $this->userDao = new UserDao();
        }
        return $this->userDao;
    }

    private function getLiveDao(): LiveDao
    {
        if ($this->liveDao === null) {
            $this->liveDao = new LiveDao();
        }
        return $this->liveDao;
    }

    public function notifyUserByWeChat(int $userId, $live): bool
    {
        $userDao = $this->getUserDao();
        $user = $userDao->findUserById($userId);
        if (!$user) {
            return false;
        }

        $now = new \DateTime('now', new \DateTimeZone('Asia/Shanghai'));
        $planTs = new \DateTime($live->planTs, new \DateTimeZone('Asia/Shanghai'));
        $diff = $now->diff($planTs);
        $word = null;
        if ($diff->invert == 0) {
            if ($diff->h > 0) {
                $word = sprintf('，您参与的直播还有%s开始', $diff->format('%h小时%i分钟'));
            } elseif ($diff->i > 0) {
                $word = sprintf('，您参与的直播还有%s开始', $diff->format('%i分钟'));
            } else {
                $word = '，您参与的直播即将开始';
            }
        } else {
            $word = '，您参与的直播已经开始';
        }

        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;

        $tmplData = [
            'first'    => ['value' => $user->username . $word, 'color' => '#D00019'],
            'keyword1' => ['value' => $live->subject, 'color' => '#173177'],
            'keyword2' => ['value' => $live->planTs, 'color' => '#173177'],
            'remark'   => ['value' => '点击进入直播，不见不散。', 'color' => '#000'],
        ];

        $customMsgData = [
            'msgtype' => 'news',
            'news'    => [
                'articles' => [
                    [
                        'title'       => $user->username . $word,
                        'description'  => "您已经报名了直播:『{$live->subject}』\n\n开播时间: {$live->planTs} \n\n点击进入直播",
                        'url'          => $url,
                        'picurl'       => $live->coverUrl ?? '',
                    ],
                ],
            ],
        ];

        if ($this->notifyLiveByWeChatCustom($user, $customMsgData)) {
            return true;
        }

        return $this->notifyByWeChat($user, 'gKSNH1PPeKQqYC4yNPjXl-OrHNdoU1jkyv7468BM6R4', $url, $tmplData);
    }

    public function sendLivesByWechat(int $userId, array $lives): bool
    {
        $userDao = $this->getUserDao();
        $user = $userDao->findUserById($userId);
        if (empty($lives)) {
            return false;
        }

        $liveArticlesArray = [];
        foreach ($lives as $value) {
            $liveArticlesArray[] = [
                "title"       => $value->subject,
                "description"  => '主播： ' . ($value->username ?? ''),
                "url"         => 'http://m.quzhiboapp.com/?liveId=' . $value->liveId,
                "picurl"      => $value->coverUrl ?? '',
            ];
        }

        $customMsgData = [
            'msgtype' => 'news',
            'news'    => ['articles' => $liveArticlesArray],
        ];

        return $this->notifyLiveByWeChatCustom($user, $customMsgData);
    }

    public function notifyNewLive(int $userId, $live): bool
    {
        $userDao = $this->getUserDao();
        $user = $userDao->findUserById($userId);
        if (!$user) {
            return false;
        }

        $tmplData = [
            'first'    => ['value' => $user->username . '，有新的直播发布啦', 'color' => '#828282'],
            'keyword1' => ['value' => $live->subject, 'color' => '#00A2C0'],
            'keyword2' => ['value' => '趣直播', 'color' => '#828282'],
            'keyword3' => ['value' => $live->owner->username ?? '', 'color' => '#00A2C0'],
            'keyword4' => ['value' => $live->planTs, 'color' => '#828282'],
            'remark'   => ['value' => '点击查看详情，回复TD0000退订新直播发布', 'color' => '#828282'],
        ];
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;
        return $this->notifyByWeChat($user, 'w2ScyoZiWxhr3j_aSrSR8McrQpIwwKvDiaMYce__NdU', $url, $tmplData);
    }

    private function notifyByWeChat($user, string $tempId, ?string $url, array $tmplData): bool
    {
        if (!$user->unionId) {
            if (function_exists('logInfo')) {
                logInfo("the user {$user->userId} do not have unionId fail send wechat msg");
            }
            return false;
        }
        $snsUserDao = $this->getSnsUserDao();
        $snsUser = $snsUserDao->getWechatSnsUser($user->unionId);
        if (!$snsUser) {
            return false;
        }
        $data = [
            'touser'      => $snsUser->openId,
            'template_id' => $tempId,
            'topcolor'    => '#FF0000',
            'data'        => $tmplData,
        ];
        if ($url) {
            $data['url'] = $url;
        }
        [$error, $res] = $this->getJsSdk()->wechatHttpPost('message/template/send', $data);
        if (!is_null($error)) {
            if (function_exists('logInfo')) {
                logInfo("wechat notify failed user: {$user->userId} name: {$user->username} error: $error");
            }
            return false;
        }
        return true;
    }

    public function notifyRefundByWeChat(int $userId, $live): bool
    {
        $userDao = $this->getUserDao();
        $user = $userDao->findUserById($userId);
        if (!$user) {
            return false;
        }
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;
        $tmplData = [
            'first'  => ['value' => $user->username . '，您好，由于技术原因，现退款第二天报名费给你', 'color' => '#000'],
            'reason' => ['value' => '两天直播归为一天', 'color' => '#173177'],
            'refund' => ['value' => '59元', 'color' => '#173177'],
            'remark' => ['value' => '如有疑问，请致电或加微信 13261630925 联系我们。点击可进入直播，晚上见。', 'color' => '#000'],
        ];
        return $this->notifyByWeChat($user, '122IpqfLsQaKMxHR0IVhiJN0YTqgEusoyJfFof-nrvk', $url, $tmplData);
    }

    public function notifyVideoByWeChat(int $userId, $live): bool
    {
        $userDao = $this->getUserDao();
        $user = $userDao->findUserById($userId);
        if (!$user) {
            return false;
        }
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;
        $tmplData = [
            'first'    => ['value' => $user->username . '，您参与的直播已经可以收看回放。', 'color' => '#000'],
            'keyword1' => ['value' => $live->owner->username ?? '', 'color' => '#000'],
            'keyword2' => ['value' => $live->subject, 'color' => '#173177'],
            'keyword3' => ['value' => $live->planTs, 'color' => '#000'],
            'remark'   => ['value' => '详情请点击。', 'color' => '#000'],
        ];
        return $this->notifyByWeChat($user, '_uG1HsFgQABk9_gK502OIaTuPEcHUAUEfRlR1cyfVFE', $url, $tmplData);
    }

    public function notifyReviewByWeChat($application): bool
    {
        $userDao = $this->getUserDao();
        $user = $userDao->findUserById($application->userId);
        if (!$user) {
            return false;
        }
        if ($application->status == defined('APPLICATION_STATUS_SUCCEED') ? APPLICATION_STATUS_SUCCEED : 1) {
            $firstWord = $user->username . '，恭喜您成为趣直播的主讲人。';
            $remark = '我们将尽快联系和协助您创建直播';
            $statusWord = '已通过';
        } else {
            $firstWord = $user->username . '，很抱歉，未能通过您的主播申请。';
            $remark = '原因: ' . ($application->reviewRemark ?? '');
            $statusWord = '未通过';
        }
        $url = 'http://m.quzhiboapp.com/#apply';
        $tmplData = [
            'first'    => ['value' => $firstWord, 'color' => '#000'],
            'keyword1' => ['value' => '无', 'color' => '#000'],
            'keyword2' => ['value' => $statusWord, 'color' => '#173177'],
            'keyword3' => ['value' => date('Y-m-d'), 'color' => '#000'],
            'remark'   => ['value' => $remark, 'color' => '#000'],
        ];
        return $this->notifyByWeChat($user, '96n9JfS5RcMraNsZcM_kQu7wyXede2gB7h77El386hM', $url, $tmplData);
    }

    public function notifyOwnerByUserPacket(int $grabUserId, int $ownerUserId, int $packetId,
                                           float $amount, string $wishing, bool $toOwner = true): bool
    {
        $userDao = $this->getUserDao();
        $grabUser = $userDao->findUserById($grabUserId);
        $owner = $userDao->findUserById($ownerUserId);
        if (!$grabUser || !$owner) {
            return false;
        }
        $result = $toOwner
            ? $grabUser->username . '抢到了您的红包'
            : $grabUser->username . '，您抢到了' . $owner->username . '的红包';
        $username = $toOwner ? $grabUser->username : $owner->username;
        $url = 'http://m.quzhiboapp.com/?type=packet&packetId=' . $packetId;
        $tmplData = [
            'first'    => ['value' => $result, 'color' => '#000'],
            'keyword1' => ['value' => number_format($amount / 100.0, 2) . '元', 'color' => '#173177'],
            'keyword2' => ['value' => $username, 'color' => '#173177'],
            'remark'   => ['value' => $wishing, 'color' => '#000'],
        ];
        return $this->notifyByWeChat($owner, 'H7LOlSlgG1O8ohoese6k4_kqwjarXAdsOgbn0x8vTQU', $url, $tmplData);
    }

    public function notifyWithdraw($withdraw, bool $systemAuto = false): bool
    {
        $userDao = $this->getUserDao();
        $adminUserId = defined('ADMIN_OP_USER_ID') ? ADMIN_OP_USER_ID : 1;
        $user = $userDao->findUserById($withdraw->userId);
        if (!$user) {
            return false;
        }
        $first = $systemAuto
            ? '感谢您邀请朋友来参加或直播获得收益，系统自动每日提现给您'
            : '您的提现申请已处理';
        $tmplData = [
            'first'    => ['value' => $first, 'color' => '#D00019'],
            'keyword1' => ['value' => $user->username, 'color' => '#000'],
            'keyword2' => ['value' => number_format($withdraw->amount / 100.0, 2) . '元', 'color' => '#00A2C0'],
            'keyword3' => ['value' => '微信账户', 'color' => '#000'],
            'keyword4' => ['value' => date('Y-m-d H:i'), 'color' => '#000'],
            'keyword5' => ['value' => '成功', 'color' => '#000'],
            'remark'   => ['value' => '已给您企业转账，请查收', 'color' => '#00A2C0'],
        ];
        return $this->notifyByWeChat($user, 'lNmzB_7IA36S_4uy0NccBexKkrTy4rybAJhAlmcSPpw', null, $tmplData);
    }

    public function notifyNewWithdraw($withdraw): bool
    {
        $userDao = $this->getUserDao();
        $adminUserId = defined('ADMIN_OP_USER_ID') ? ADMIN_OP_USER_ID : 1;
        if (env('WECHAT_DEBUG') === 'true') {
            $targetUser = $userDao->findUserById($withdraw->userId);
        } else {
            $targetUser = $userDao->findUserById($adminUserId);
        }
        if (!$targetUser) {
            return false;
        }
        $withdrawUser = $userDao->findUserById($withdraw->userId);
        if (!$withdrawUser) {
            return false;
        }
        $tmplData = [
            'first'    => ['value' => '有新的提现申请', 'color' => '#000'],
            'keyword1' => ['value' => $withdrawUser->username ?? '', 'color' => '#000'],
            'keyword2' => ['value' => date('Y-m-d H:i'), 'color' => '#000'],
            'keyword3' => ['value' => number_format($withdraw->amount / 100.0, 2) . '元', 'color' => '#00A2C0'],
            'keyword4' => ['value' => '微信账户', 'color' => '#000'],
            'remark'   => ['value' => '提现ID为:' . $withdraw->withdrawId . '，请尽快处理', 'color' => '#00A2C0'],
        ];
        return $this->notifyByWeChat($targetUser, 'fD-twBRM96P4FkBSZQHPJ4GJ8_1i9N7HaDe7A36CllY', null, $tmplData);
    }

    public function notifyNewReview($live): bool
    {
        $userDao = $this->getUserDao();
        $adminUserId = defined('ADMIN_OP_USER_ID') ? ADMIN_OP_USER_ID : 1;
        if (env('WECHAT_DEBUG') === 'true') {
            $targetUser = $userDao->findUserById($live->ownerId);
        } else {
            $targetUser = $userDao->findUserById($adminUserId);
        }
        if (!$targetUser) {
            return false;
        }
        $liveOwner = $userDao->findUserById($live->ownerId);
        if (!$liveOwner) {
            return false;
        }
        $tmplData = [
            'first'    => ['value' => ($liveOwner->username ?? '') . '提交了直播，等待审核', 'color' => '#000'],
            'keyword1' => ['value' => $live->subject, 'color' => '#000'],
            'keyword2' => ['value' => '等待审核', 'color' => '#000'],
            'remark'   => ['value' => '直播ID为:' . $live->liveId . '，请尽快处理', 'color' => '#00A2C0'],
        ];
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;
        return $this->notifyByWeChat($targetUser, 'NN03-O5T23mawo9yado4EV-ycEM-I2-Q92VwLd4pifM', $url, $tmplData);
    }

    public function notifyNewIncome(int $incomeType, float $amount, $live, $fromUser, ?int $inviteFromUserId = null): bool
    {
        if ($this->inDisturbHour()) {
            if (function_exists('logInfo')) {
                logInfo("notifyNewIncome inDisturbHour $incomeType, $amount, {$live->ownerId}, {$fromUser->userId}");
            }
            return false;
        }
        $userDao = $this->getUserDao();
        $attendUser = $userDao->findUserById($fromUser->userId);
        $liveOwner = $userDao->findUserById($live->ownerId);
        $inviteFromUser = $inviteFromUserId ? $userDao->findUserById($inviteFromUserId) : null;

        $notifyUser = null;
        $incomeTypeWord = '';
        $actionWord = '';
        if ($incomeType == (defined('TRANS_TYPE_LIVE_INCOME') ? TRANS_TYPE_LIVE_INCOME : 1)) {
            $incomeTypeWord = '赞助直播';
            $actionWord = sprintf('%s赞助了您的直播《%s》', $attendUser->username ?? '', $live->subject);
            $notifyUser = $liveOwner;
        } elseif ($incomeType == (defined('TRANS_TYPE_REWARD_INCOME') ? TRANS_TYPE_REWARD_INCOME : 2)) {
            $incomeTypeWord = '打赏直播';
            $actionWord = sprintf('%s打赏了您的直播《%s》', $attendUser->username ?? '', $live->subject);
            $notifyUser = $liveOwner;
        } elseif ($incomeType == (defined('TRANS_TYPE_INVITE_INCOME') ? TRANS_TYPE_INVITE_INCOME : 3)) {
            $incomeTypeWord = '邀请参加直播';
            $actionWord = sprintf('%s参加了您分享的直播《%s》', $attendUser->username ?? '', $live->subject);
            $notifyUser = $inviteFromUser;
        }

        if (!$notifyUser) {
            return false;
        }
        if (defined('KEY_INCOME_SUBSCRIBE') && !$notifyUser->incomeSubscribe) {
            if (function_exists('logInfo')) {
                logInfo("{$notifyUser->userId} unsubscribe income");
            }
            return false;
        }

        $tmplData = [
            'first'    => ['value' => sprintf('%s，您获得%s元收益', $actionWord, number_format($amount / 100.0, 2)), 'color' => '#D00019'],
            'keyword1' => ['value' => $incomeTypeWord, 'color' => '#000'],
            'keyword2' => ['value' => date('Y-m-d H:i'), 'color' => '#000'],
            'remark'   => ['value' => '您的努力初见成效，再接再励哟。回复TD0001退订收益提醒', 'color' => '#000'],
        ];
        $url = 'http://m.quzhiboapp.com/#account';
        return $this->notifyByWeChat($notifyUser, 'Clt9LxKunzjbXwMOYAOoB6w_-40u9FUdv7Men4vTluc', $url, $tmplData);
    }

    private function notifyLiveByWeChatCustom($user, array $data): bool
    {
        if (!$user->unionId) {
            if (function_exists('logInfo')) {
                logInfo("the user {$user->userId} do not have unionId fail send wechat msg");
            }
            return false;
        }
        $snsUserDao = $this->getSnsUserDao();
        $snsUser = $snsUserDao->getWechatSnsUser($user->unionId);
        if (!$snsUser) {
            return false;
        }
        $data['touser'] = $snsUser->openId;
        [$error, $res] = $this->getJsSdk()->wechatHttpPost('message/custom/send', $data);
        if (!is_null($error)) {
            if (function_exists('logInfo')) {
                logInfo("wechat custom notify failed errcode != 0 user: {$user->userId} name: {$user->username} code: $res error: $error");
            }
            return false;
        }
        return true;
    }

    public function notifyUserAttendanceSuccessByWeChat(int $userId, $live): bool
    {
        $userDao = $this->getUserDao();
        $user = $userDao->findUserById($userId);
        if (!$user) {
            return false;
        }
        $word = '，您参与的直播已经报名成功';
        $url = 'http://m.quzhiboapp.com/?liveId=' . $live->liveId;

        $tmplData = [
            'first'    => ['value' => $user->username . $word, 'color' => '#D00019'],
            'keyword1' => ['value' => $user->username, 'color' => '#173177'],
            'keyword2' => ['value' => $live->subject, 'color' => '#173177'],
            'keyword3' => ['value' => $live->planTs, 'color' => '#173177'],
            'keyword4' => ['value' => $live->liveId, 'color' => '#173177'],
            'remark'   => ['value' => '点击进入直播，不见不散。', 'color' => '#000'],
        ];

        $customMsgData = [
            'msgtype' => 'news',
            'news'    => [
                'articles' => [
                    [
                        'title'       => $user->username . $word,
                        'description'  => "您已经成功报名直播:『{$live->subject}』\n\n开播时间: {$live->planTs} \n\n点击进入直播",
                        'url'          => $url,
                        'picurl'       => $live->coverUrl ?? '',
                    ],
                ],
            ],
        ];

        if ($this->notifyLiveByWeChatCustom($user, $customMsgData)) {
            return true;
        }
        return $this->notifyByWeChat($user, 'o6Mtcjn4w2aQ7DqwyQdFcwzC57Sr8-O1sK-5s_kuG9Q', $url, $tmplData);
    }

    private function inDisturbHour(int $nightHour = 23, int $morningHour = 9): bool
    {
        $hour = (int)(new \DateTime('now', new \DateTimeZone('Asia/Shanghai')))->format('H');
        return ($hour >= $nightHour) || ($hour < $morningHour);
    }
}
