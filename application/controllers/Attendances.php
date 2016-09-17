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
    public $pay;
    public $snsUserDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(AttendanceDao::class);
        $this->attendanceDao = new AttendanceDao();
        $this->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
        $this->load->library(Pay::class);
        $this->pay = new Pay();
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_LIVE_ID, KEY_CHANNEL))) {
            return;
        }
        $liveId = $this->post(KEY_LIVE_ID);
        $channel = $this->post(KEY_CHANNEL);
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
        if ($live->attendanceCount >= $live->maxPeople) {
            $this->failure(ERROR_EXCEED_MAX_PEOPLE);
            return;
        }
        $attendance = $this->attendanceDao->getAttendance($user->userId, $liveId);
        if ($attendance != null) {
            $this->failure(ERROR_ALREADY_ATTEND);
            return;
        }
        $openId = null;
        if ($channel == CHANNEL_WECHAT_H5) {
            $snsUser = $this->snsUserDao->getSnsUserByUserId($user->userId);
            if (!$snsUser) {
                $this->failure(ERROR_MUST_BIND_WECHAT);
                return;
            }
            $openId = $snsUser->openId;
        }
        // max 24 chars
        $subject = truncate($user->username, 12) . '参加直播' . truncate($live->subject, 12);
        $body = $user->username . ' 参加直播 ' . $live->subject;
        $metaData = array(KEY_LIVE_ID => $liveId, KEY_USER_ID => $user->userId);
        $ch = $this->createChargeAndInsert($live->amount, $channel, $subject, $body,
            $metaData, $user, $openId);
        if ($ch == null) {
            $this->failure(ERROR_CHARGE_CREATE);
            return;
        }
        $this->succeed($ch);
    }

    protected function createChargeAndInsert($amount, $channel, $subject, $body,
                                             $metaData, $user, $openId)
    {
        $orderNo = genOrderNo();
        $ipAddress = $this->input->ip_address();
        if ($ipAddress == '::1') {
            // local debug case
            $ipAddress = '127.0.0.1';
        }
        $ch = $this->pay->createCharge($orderNo, $channel, $amount, $subject, $body, $openId);
        if ($ch == null) {
            return null;
        }
        $id = $this->chargeDao->add($orderNo, $amount, $channel, $user->userId, $ipAddress, $metaData);
        if (!$id) {
            return null;
        }
        return $ch;
    }

    function one_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_LIVE_ID))) {
            return;
        }
        $liveId = $this->get(KEY_LIVE_ID);
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

    function myList_get()
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
