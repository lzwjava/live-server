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
    public $shareDao;
    public $weChatPlatform;

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
        $this->load->model(ShareDao::class);
        $this->shareDao = new ShareDao();
        $this->load->library(WeChatPlatform::class);
        $this->weChatPlatform = new WeChatPlatform();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_LIVE_ID))) {
            return;
        }
        $liveId = $this->post(KEY_LIVE_ID);
        $fromUserId = intval($this->post(KEY_FROM_USER_ID));
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId, $user);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($live->status < LIVE_STATUS_WAIT) {
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
        if ($fromUserId == $user->userId) {
            $fromUserId = null;
        }
        if ($fromUserId) {
            $fromUser = $this->userDao->findUserById($fromUserId);
            if ($this->checkIfObjectNotExists($fromUser)) {
                return;
            }
        }
        $attendance = $this->attendanceDao->getAttendance($user->userId, $liveId);
        if ($attendance != null) {
            $this->failure(ERROR_ALREADY_ATTEND);
            return;
        }
        if ($live->needPay) {
            $channel = $this->post(KEY_CHANNEL);
            if ($this->checkIfNotInArray($channel, channelSet())
            ) {
                return;
            }

            list($error, $openId) = $this->snsUserDao->getOpenIdByChannel($user, $channel);
            if ($error) {
                $this->failure($error);
                return;
            }

            // max 24 chars
            $subject = '参加直播';
            $body = $user->username . ' 参加直播 ' . $live->subject;
            $metaData = array(KEY_TYPE => CHARGE_TYPE_ATTEND,
                KEY_LIVE_ID => $liveId, KEY_USER_ID => $user->userId);
            if ($fromUserId && $fromUserId > 0) {
                $metaData[KEY_FROM_USER_ID] = $fromUserId;
            }

            list($error, $ch) = $this->pay->createChargeAndInsert($live->realAmount, $channel,
                $subject, $body, $metaData, $user, $openId);
            if ($error) {
                $this->failure(ERROR_CHARGE_CREATE, $error);
                return;
            }
            $this->succeed($ch);
        } else {
            $id = $this->attendanceDao->addAttendanceAndIncreaseCount($user->userId,
                $live->liveId, null, $fromUserId);
            $this->succeed($id);
        }
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

    function invites_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_LIVE_ID))) {
            return;
        }
        $liveId = $this->get(KEY_LIVE_ID);
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $skip = $this->skip();
        $limit = $this->limit();
        $inviteUsers = $this->attendanceDao->queryInviteList($liveId, $skip, $limit);
        $this->succeed($inviteUsers);
    }

    function refund_get($liveId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $attendances = $this->attendanceDao->getAttendancesByLiveId($liveId, 0, 10000);
        $succeedCount = 0;
        $total = count($attendances);
        foreach ($attendances as $attendance) {
            $charge = $this->chargeDao->getOneByOrderNo($attendance->orderNo);
            $ok = $this->pay->refund($charge);
            if ($ok) {
                $succeedCount++;
                $this->weChatPlatform->notifyRefundByWeChat($attendance->userId, $live);
            }
        }
        logInfo('succeedCount:' . $succeedCount . ' total:' . $total);
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => $total));
    }

    function transfer_get()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_USER_ID,
            KEY_AMOUNT))
        ) {
            return;
        }
        $userId = $this->get(KEY_USER_ID);
        $amount = $this->get(KEY_AMOUNT);
        $user = $this->userDao->findUserById($userId);
        if ($this->checkIfObjectNotExists($user)) {
            return;
        }
        $snsUser = $this->snsUserDao->getSnsUserByUser($user);
        if (!$snsUser) {
            $this->failure(ERROR_SNS_USER_NOT_EXISTS);
            return;
        }
        list($transOk, $transErr) = $this->pay->transfer($snsUser->openId,
            $amount, '手动转账');
        if ($transErr) {
            $this->failure($transErr);
            return;
        }
        $this->succeed();
    }

}
