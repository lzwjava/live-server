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

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
        $this->load->model(AttendanceDao::class);
        $this->attendanceDao = new AttendanceDao();
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
            $this->db->trans_start();
            $this->chargeDao->updateChargeToPaid($orderNo);
            $charge = $this->chargeDao->getOneByOrderNo($orderNo);
            $this->attendanceDao->addAttendance($userId, $liveId, $charge->chargeId);
            $this->liveDao->incrementAttendanceCount();
            $this->db->trans_complete();
            if (!$this->db->trans_status()) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
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
