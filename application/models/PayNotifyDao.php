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
    }

    function handleChargeSucceed($orderNo)
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
            $error = $this->transactionDao->newAlipayRecharge($userId, $orderNo, $amount, $charge->chargeId);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }
            $this->attendanceDao->addAttendance($userId, $liveId, $orderNo);
            $this->liveDao->incrementAttendanceCount();
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
            $this->db->trans_commit();
            return null;
        } else {
            return ERROR_PARAMETER_ILLEGAL;
        }
    }
}