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
    public $alipayDao;

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
        $this->load->model(Alipay::class);
        $this->alipayDao = new Alipay();
    }

    public function notify_post()
    {
        $content = file_get_contents("php://input");
        logInfo("notify $content");
        if ($this->alipayDao->isSignVerify($_POST, $_POST['sign'])) {
            $outTradeNo = $_POST['out_trade_no'];
            $trade_status = $_POST['trade_status'];
            if ($trade_status == 'TRADE_SUCCESS') {
                $error = $this->handleChargeSucceed($outTradeNo);
                if ($error) {
                    logInfo("error: " . $error);
                    $this->failure($error);
                    return;
                }
            }
            echo 'success';
        } else {
            logInfo("sign failed");
            $this->failure(ERROR_SIGN_FAILED);
        }
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

    private function handleChargeSucceed($orderNo)
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
            $error = $this->transactionDao->newPay($userId, $this->genOrderNo(),
                -$amount, $liveId, $live->owner->username);
            if ($error || !$this->db->trans_status()) {
                $this->db->trans_rollback();
                return $error;
            }
            $user = $this->userDao->findPublicUserById($userId);
            $error = $this->transactionDao->newIncome($live->ownerId, $this->genOrderNo(), $amount, $liveId,
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

    function success_get()
    {
        $params = $this->get();
        $paramsStr = json_encode($params);
        logInfo("reward success $paramsStr");
    }

}
