<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/14/16
 * Time: 3:06 AM
 */
class PayNotifyDao extends BaseDao
{

    /** @var LiveDao */
    public $liveDao;
    /** @var AttendanceDao */
    public $attendanceDao;
    /** @var ChargeDao */
    public $chargeDao;
    /** @var TransactionDao */
    public $transactionDao;
    /** @var ShareDao */
    public $shareDao;
    /** @var UserDao */
    public $userDao;
    /** @var RewardDao */
    public $rewardDao;
    /** @var PacketDao */
    public $packetDao;
    /** @var WithdrawDao */
    public $withdrawDao;
    /** @var Pay */
    public $pay;
    /** @var SnsUserDao */
    public $snsUserDao;
    /** @var WeChatPlatform */
    public $weChatPlatform;
    /** @var AccountDao */
    public $accountDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
        $this->load->model(AttendanceDao::class);
        $this->attendanceDao = new AttendanceDao();
        $this->load->model(TransactionDao::class);
        $this->transactionDao = new TransactionDao();
        $this->load->model(ShareDao::class);
        $this->shareDao = new ShareDao();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
        $this->load->model(RewardDao::class);
        $this->rewardDao = new RewardDao();
        $this->load->model(PacketDao::class);
        $this->packetDao = new PacketDao();
        $this->load->model(WithdrawDao::class);
        $this->withdrawDao = new WithdrawDao();
        $this->load->library(Pay::class);
        $this->pay = new Pay();
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $this->load->library(WeChatPlatform::class);
        $this->weChatPlatform = new WeChatPlatform();
        $this->load->model(AccountDao::class);
        $this->accountDao = new AccountDao();
    }

    private function remarkFromChannel($channel)
    {
        if ($channel == CHANNEL_ALIPAY_APP) {
            return REMARK_ALIPAY;
        } else if ($channel == CHANNEL_WECHAT_H5) {
            return REMARK_WECHAT;
        } else if ($channel == CHANNEL_WECHAT_QRCODE) {
            return REMARK_WECHAT_QRCODE;
        } else if ($channel == CHANNEL_APPLE_IAP) {
            return REMARK_APPLE_IAP;
        }
        return 'unknown';
    }

    private function updatePaidAndNewCharge($orderNo, $charge, $channel, $userId)
    {
        $this->chargeDao->updateChargeToPaid($orderNo);
        $amount = $charge->amount;
        $remark = $this->remarkFromChannel($channel);
        $error = $this->transactionDao->newCharge($userId, $orderNo, $amount, $charge->chargeId, $remark);
        return $error;
    }

    function handleChargeSucceed($orderNo)
    {
        $charge = $this->chargeDao->getOneByOrderNo($orderNo);
        $channel = $charge->channel;
        $amount = $charge->amount;
        if ($charge == null) {
            return ERROR_OBJECT_NOT_EXIST;
        }
        if ($charge->paid == 1) {
            return ERROR_ALREADY_NOTIFY;
        }
        $metadata = json_decode($charge->metaData);
        $type = $metadata->type;
        if ($type == CHARGE_TYPE_ATTEND) {
            $liveId = $metadata->liveId;
            $userId = $metadata->userId;
            $inviteFromUserId = null;
            if (isset($metadata->fromUserId)) {
                $inviteFromUserId = $metadata->fromUserId;
            }
            $this->db->trans_begin();
            $error = $this->updatePaidAndNewCharge($orderNo, $charge, $channel, $userId);
            if ($error) {
                $this->db->trans_rollback();
                return $error;
            }

            $attendanceId = $this->attendanceDao->addAttendanceAndIncreaseCount($userId,
                $liveId, $orderNo, $inviteFromUserId);
            if (!$attendanceId) {
                $this->db->trans_rollback();
                return ERROR_SQL_WRONG;
            }

            $live = $this->liveDao->getLive($liveId);
            $fromUser = $this->userDao->findPublicUserById($userId);
            $toUser = $live->owner;
            $error = $this->payUser($fromUser, $toUser, $live, $amount, $type,
                $inviteFromUserId);
            if ($error) {
                $this->db->trans_rollback();
                return $error;
            }
            $this->shareDao->useToDiscount($userId, $liveId);

            $this->db->trans_commit();
            return null;
        } else if ($type == CHARGE_TYPE_REWARD) {
            $liveId = $metadata->liveId;
            $userId = $metadata->userId;
            $this->db->trans_begin();
            $error = $this->updatePaidAndNewCharge($orderNo, $charge, $channel, $userId);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }
            $attendanceId = $this->rewardDao->addReward($userId, $liveId, $orderNo);
            if (!$attendanceId || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return ERROR_SQL_WRONG;
            }

            $amount = $charge->amount;

            $live = $this->liveDao->getLive($liveId);
            $fromUser = $this->userDao->findPublicUserById($userId);
            $toUser = $live->owner;
            $error = $this->payUser($fromUser, $toUser, $live, $amount, $type);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }

            $this->db->trans_commit();
            return null;
        } else if ($type == CHARGE_TYPE_PACKET) {
            $userId = $metadata->userId;
            $totalAmount = $metadata->totalAmount;
            $totalCount = $metadata->totalCount;
            $wishing = $metadata->wishing;
            $this->db->trans_begin();
            $error = $this->updatePaidAndNewCharge($orderNo, $charge, $channel, $userId);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }

            $attendanceId = $this->packetDao->addPacket($userId, $totalAmount, $totalCount,
                $wishing, $orderNo);
            if (!$attendanceId || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return ERROR_SQL_WRONG;
            }
            $error = $this->transactionDao->newPayPacket($userId, genOrderNo(), $totalAmount,
                $attendanceId, '发红包');
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }
            $this->db->trans_commit();
            return null;
        } else if ($type == CHARGE_TYPE_BALANCE) {
            $userId = $metadata->userId;
            $this->db->trans_begin();
            $error = $this->updatePaidAndNewCharge($orderNo, $charge, $channel, $userId);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }
            $this->db->trans_commit();
            return null;
        } else {
            return ERROR_PARAMETER_ILLEGAL;
        }
    }

    private function payUser($fromUser, $toUser, $live, $amount, $type,
                             $inviteFromUserId = null)
    {
        $remark = null;
        $incomeType = null;
        $liveId = $live->liveId;
        $anchorAmount = 0;
        $systemAmount = 0;
        $inviteAmount = 0;
        if ($type == CHARGE_TYPE_ATTEND) {
            $remark = sprintf(REMARK_ATTEND, $fromUser->username, $toUser->username);
            $incomeType = TRANS_TYPE_LIVE_INCOME;
            $anchorAmount = floor($amount * ANCHOR_INCOME_RATE);
            if ($inviteFromUserId) {
                $inviteAmount = floor($amount * INVITE_INCOME_RATE);
                $systemAmount = $amount - $anchorAmount - $inviteAmount;
            } else {
                $systemAmount = $amount - $anchorAmount;
            }
        } else if ($type == CHARGE_TYPE_REWARD) {
            $remark = sprintf(REMARK_REWARD, $fromUser->username, $toUser->username);
            $incomeType = TRANS_TYPE_REWARD_INCOME;
            $anchorAmount = floor($amount * ANCHOR_INCOME_REWARD_RATE);
            $systemAmount = $amount - $anchorAmount;
        }
        $error = $this->transactionDao->newPay($fromUser->userId, genOrderNo(),
            -$amount, $liveId, $remark);
        if ($error) {
            return $error;
        }
        // 分钱
        if ($anchorAmount > 0) {
            $error = $this->transactionDao->newIncome($toUser->userId, genOrderNo(),
                $anchorAmount, $incomeType, $liveId, $remark);
            if ($error) {
                return $error;
            }
        }
        if ($inviteAmount > 0) {
            $error = $this->transactionDao->newIncome($inviteFromUserId, genOrderNo(),
                $inviteAmount, TRANS_TYPE_INVITE_INCOME, $liveId, REMARK_INVITE_INCOME);
            if ($error) {
                return $error;
            }
        }
        if ($systemAmount > 0) {
            $error = $this->transactionDao->newIncome(ADMIN_OP_SYSTEM_ID, genOrderNo(),
                $systemAmount, $incomeType, $liveId, $remark . '的系统分成');
            if ($error) {
                return $error;
            }
        }
        // 主播通知
        if ($anchorAmount > 0) {
            $this->weChatPlatform->notifyNewIncome($incomeType, $anchorAmount, $live,
                $fromUser);
        }
        // 邀请者通知
        if ($inviteAmount > 0) {
            $this->weChatPlatform->notifyNewIncome(TRANS_TYPE_INVITE_INCOME, $inviteAmount,
                $live, $fromUser, $inviteFromUserId);
        }

        return null;
    }

    function createWithdraw($user, $amount)
    {
        if (!$user->wechatSubscribe) {
            return array(ERROR_MUST_SUBSCRIBE, null);
        }
        $snsUser = $this->snsUserDao->getSnsUserByUser($user);
        if (!$snsUser) {
            return array(ERROR_SNS_USER_NOT_EXISTS, null);
        }
        $account = $this->accountDao->getOrCreateAccount($user->userId);
        if ($amount > $account->balance) {
            return array(ERROR_EXCEED_BALANCE, null);
        }
        if ($amount < MIN_WITHDRAW_AMOUNT) {
            return array(ERROR_WITHDRAW_AMOUNT_TOO_LITTLE, null);
        }
        $haveWaitWithdraw = $this->withdrawDao->haveWaitWithdraw($user->userId);
        if ($haveWaitWithdraw) {
            return array(ERROR_HAVE_WAIT_WITHDRAW, null);
        }
        $haveWaitLive = $this->liveDao->haveWaitLive($user->userId);
        if ($haveWaitLive) {
            return array(ERROR_HAVE_WAIT_LIVE, null);
        }
        $withdrawId = $this->withdrawDao->createWithdraw($user->userId, $amount);
        if (!$withdrawId) {
            return array(ERROR_SQL_WRONG, null);
        }
        $withdraw = $this->withdrawDao->queryWithdraw($withdrawId);
        $this->weChatPlatform->notifyNewWithdraw($withdraw);
        $data = array(KEY_WITHDRAW_ID => $withdrawId);
        return array(null, $data);
    }

    function manualWithdraw($userId, $amount, $transfer)
    {
        $user = $this->userDao->findUserById($userId);
        if (!$user) {
            return array(ERROR_OBJECT_NOT_EXIST, null);
        }
        list($error, $data) = $this->createWithdraw($user, $amount);
        if ($error) {
            return array($error, null);
        }
        $withdrawId = $data[KEY_WITHDRAW_ID];
        $error = $this->handleWithdraw($withdrawId, $transfer, false, false);
        if ($error) {
            return array($error, null);
        }
        return array(null, $data);
    }

    function handleWithdraw($withdrawId, $transfer = true, $systemAuto = false,
                            $notify = true)
    {
        $withdraw = $this->withdrawDao->queryWithdraw($withdrawId);
        if ($withdraw->status != WITHDRAW_STATUS_WAIT) {
            return ERROR_WITHDRAW_ALREADY_DONE;
        }
        $this->db->trans_begin();
        $error = $this->transactionDao->newWithdraw($withdraw->userId,
            $withdraw->amount, $withdraw->withdrawId);
        if ($error) {
            $this->db->trans_rollback();
            return $error;
        }
        $ok = $this->withdrawDao->finishWithdraw($withdraw->withdrawId);
        if (!$ok) {
            $this->db->trans_rollback();
            return ERROR_SQL_WRONG;
        }
        if ($transfer) {
            $user = $this->userDao->findUserById($withdraw->userId);
            $snsUser = $this->snsUserDao->getSnsUserByUser($user);
            $transOk = null;
            $transErr = null;
            try {
                list($transOk, $transErr) = $this->pay->sendIncomePacket($snsUser->openId, $withdraw->amount);
            } catch (Exception $e) {
                $transOk = false;
                $transErr = $e->getMessage();
            }
            if (!$transOk) {
                $this->db->trans_rollback();
                return $transErr;
            }
            if ($notify) {
                $this->weChatPlatform->notifyWithdraw($withdraw, $systemAuto);
            }
            $this->db->trans_commit();
            return null;
        } else {
            $this->db->trans_commit();
            return null;
        }
    }
}
