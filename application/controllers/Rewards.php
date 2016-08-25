<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午8:32
 */
class Rewards extends BaseController
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

    public function notify_post()
    {
        $content = file_get_contents("php://input");
        logInfo("notify $content");
        echo 'success';
    }

    public function callback_post()
    {
        $content = file_get_contents("php://input");
        logInfo("content $content");
        $event = json_decode($content);
        if (!isset($event->type)) {
            $this->failure(ERROR_MISS_PARAMETERS, "please input event type");
            return;
        }
        switch ($event->type) {
            case 'charge.succeeded':
                // 开发者在此处加入对支付异步通知的处理代码
                $this->handleChargeSucceed($event);
                break;
            case "refund.succeeded":
                // 开发者在此处加入对退款异步通知的处理代码
                header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                break;
            default:
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                break;
        }
    }

    private function handleChargeSucceed($event)
    {
        if (!isset($event->data) || !isset($event->data->object) ||
            !isset($event->data->object->order_no)
        ) {
            $this->failure(ERROR_PARAMETER_ILLEGAL, "there are no orderNo in event");
            return;
        }
        $object = $event->data->object;
        $orderNo = $object->order_no;
        $charge = $this->chargeDao->getOneByOrderNo($orderNo);
        if ($charge == null) {
            $this->failure(ERROR_OBJECT_NOT_EXIST);
            return;
        }
        $metadata = $object->metadata;
        if (isset($metadata->liveId)) {
            $liveId = $metadata->liveId;
            $userId = $metadata->userId;
            $this->db->trans_begin();

            $this->chargeDao->updateChargeToPaid($orderNo);
            $charge = $this->chargeDao->getOneByOrderNo($orderNo);
            $amount = $charge->amount;
            $error = $this->transactionDao->newAlipayRecharge($userId, $orderNo, $amount, $charge->chargeId);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                $this->failure($error);
                return;
            }

            $this->attendanceDao->addAttendance($userId, $liveId, $orderNo);
            $this->liveDao->incrementAttendanceCount();
            $live = $this->liveDao->getLive($liveId);
            $error = $this->transactionDao->newPay($userId, $this->genOrderNo(),
                -$amount, $liveId, $live->owner->username);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                $this->failure($error);
                return;
            }
            $user = $this->userDao->findPublicUserById($userId);
            $error = $this->transactionDao->newIncome($live->ownerId, $this->genOrderNo(), $amount, $liveId,
                $user->username);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                $this->failure($error);
                return;
            }
            $this->db->trans_commit();
            $this->succeed();
        } else {
            $this->failure(ERROR_PARAMETER_ILLEGAL, "not set liveId in metadata");
        }
    }

    public function success_get()
    {
        $params = $this->get();
        $paramsStr = json_encode($params);
        logInfo("reward success $paramsStr");
    }
}
