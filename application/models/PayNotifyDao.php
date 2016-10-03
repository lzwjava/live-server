<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/14/16
 * Time: 3:06 AM
 */
class PayNotifyDao extends BaseDao
{

    public $liveDao;
    public $attendanceDao;
    public $chargeDao;
    public $transactionDao;
    public $shareDao;

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
    }

    private function remarkFromChannel($channel)
    {
        if ($channel == CHANNEL_ALIPAY_APP) {
            return REMARK_ALIPAY;
        } else if ($channel == CHANNEL_WECHAT_H5) {
            return REMARK_WECHAT;
        }
        return 'unknown';
    }

    function handleChargeSucceed($orderNo, $channel)
    {
        $charge = $this->chargeDao->getOneByOrderNo($orderNo);
        if ($charge == null) {
            return ERROR_OBJECT_NOT_EXIST;
        }
        if ($charge->paid == 1) {
            return ERROR_ALREADY_NOTIFY;
        }
        $metadata = json_decode($charge->metaData);
        if (isset($metadata->liveId)) {
            $liveId = $metadata->liveId;
            $userId = $metadata->userId;
            $this->db->trans_begin();
            $this->chargeDao->updateChargeToPaid($orderNo);
            $amount = $charge->amount;
            $remark = $this->remarkFromChannel($channel);
            $error = $this->transactionDao->newCharge($userId, $orderNo, $amount, $charge->chargeId, $remark);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }
            $ok = $this->attendanceDao->addAttendance($userId, $liveId, $orderNo);
            if (!$ok || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return ERROR_SQL_WRONG;
            }

            $ok = $this->liveDao->incrementAttendanceCount($liveId);
            if (!$ok || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return ERROR_SQL_WRONG;
            }

            $live = $this->liveDao->getLive($liveId);
            $error = $this->transactionDao->newPay($userId, genOrderNo(),
                -$amount, $liveId, $live->owner->username);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }
            $user = $this->userDao->findPublicUserById($userId);
            $error = $this->transactionDao->newIncome($live->ownerId, genOrderNo(), $amount, $liveId,
                $user->username);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }
            $this->shareDao->useToDiscount($userId, $liveId);

            $this->db->trans_commit();
            return null;
        } else {
            return ERROR_PARAMETER_ILLEGAL;
        }
    }
}
