<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 下午7:33
 */
class Attendances extends BaseController
{
    public $attendanceDao;
    public $liveDao;
    public $chargeDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(AttendanceDao::class);
        $this->attendanceDao = new AttendanceDao();
        $this->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_LIVE_ID))) {
            return;
        }
        $liveId = $this->post(KEY_LIVE_ID);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($live->status == LIVE_STATUS_PREPARE || $live->status == LIVE_STATUS_OFF) {
            $this->failure(ERROR_NOT_ALLOW_ATTEND);
            return;
        }
        if ($live->ownerId == $user->userId) {
            $this->failure(ERROR_OWNER_CANNOT_ATTEND);
            return;
        }
        $attendance = $this->attendanceDao->getAttendance($user->userId, $liveId);
        if ($attendance != null) {
            $this->failure(ERROR_ALREADY_ATTEND);
            return;
        }
        $subject = truncate($user->username, 18) . '参加直播' . $live->liveId;
        $body = $user->username . ' 参加 ' . $live->subject;
        $metaData = array(KEY_LIVE_ID => $liveId, KEY_USER_ID => $user->userId);
        $ch = $this->createPingPPCharge($live->amount, $subject, $body, $metaData, $user);
        if ($ch == null) {
            $this->failure(ERROR_PINGPP_CHARGE);
            return;
        }
        $this->succeed($ch);
    }

    private function getOrderNo()
    {
        return getToken(16);
    }

    protected function createPingPPCharge($amount, $subject, $body, $metaData, $user)
    {
        $orderNo = $this->getOrderNo();
        if (isLocalDebug()) {
            \Pingpp\Pingpp::setApiKey('sk_test_nz9af5CKmb5CnXn10Ou1eHq5');
        } else {
            \Pingpp\Pingpp::setApiKey('sk_live_SSijL0KO8eHK5qzfPG0mjDW9');
        }
        if (isLocalDebug()) {
            // CodeReviewTest
            $appId = 'app_nn9qHKPafHCSDKq5';
        } else {
            // CodeReviewProd
            $appId = 'app_jTSKu5CmXbHC0q5q';
        }
        $ipAddress = $this->input->ip_address();
        if ($ipAddress == '::1') {
            // local debug case
            $ipAddress = '127.0.0.1';
        }
        $ch = \Pingpp\Charge::create(
            array(
                'order_no' => $orderNo,
                'app' => array('id' => $appId),
                'channel' => 'alipay_pc_direct',
                'amount' => $amount,
                'client_ip' => $ipAddress,
                'currency' => 'cny',
                'subject' => $subject,
                'body' => $body,
                'metadata' => $metaData,
                'extra' => array('success_url' => 'http://api.reviewcode.cn/rewards/success')
            )
        );
        if ($ch == null || $ch->failure_code != null) {
            logInfo("charge create failed\n");
            if ($ch != null) {
                logInfo("reason $ch->failure_message");
            }
            return null;
        }
        $this->chargeDao->add($orderNo, $amount, $user->userId, $ipAddress);
        return $ch;
    }

    function one_get($liveId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $attendance = $this->attendanceDao->getAttendance($user->userId, $liveId);
        if ($this->checkIfObjectNotExists($attendance)) {
            return;
        }
        $this->succeed($attendance);
    }

    function list_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $skip = $this->skip();
        $limit = $this->limit();
        $attendances = $this->attendanceDao->getAttendancesByUserId($user->userId, $skip, $limit);
        $this->succeed($attendances);
    }

    function liveList_get($liveId)
    {
        $skip = $this->skip();
        $limit = $this->limit();
        $attendances = $this->attendanceDao->getAttendancesByLiveId($liveId, $skip, $limit);
        $this->succeed($attendances);
    }
}
