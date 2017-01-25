<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/28/16
 * Time: 12:46 AM
 */
class Lives extends BaseController
{
    public $liveDao;
    public $statusDao;
    public $sms;
    public $attendanceDao;
    public $weChatPlatform;
    public $couponDao;
    public $videoDao;
    public $chargeDao;
    public $weChatAppClient;
    public $qiniuLive;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(StatusDao::class);
        $this->statusDao = new StatusDao();
        $this->load->library(Sms::class);
        $this->sms = new Sms();
        $this->load->model(AttendanceDao::class);
        $this->attendanceDao = new AttendanceDao();
        $this->load->library(WeChatPlatform::class);
        $this->weChatPlatform = new WeChatPlatform();
        $this->load->model(CouponDao::class);
        $this->couponDao = new CouponDao();
        $this->load->model(VideoDao::class);
        $this->videoDao = new VideoDao();
        $this->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
        $this->load->library(WeChatAppClient::class);
        $this->weChatAppClient = new WeChatAppClient();
        $this->load->library(QiniuLive::class);
        $this->qiniuLive = new QiniuLive();
    }

    protected function checkIfAmountWrong($amount)
    {
        if (is_int($amount) == false) {
            $this->failure(ERROR_AMOUNT_UNIT);
            return true;
        }
        if ($amount < LEAST_COMMON_PAY) {
            $this->failure(ERROR_AMOUNT_TOO_LITTLE);
            return true;
        }
        if ($amount > MAX_COMMON_PAY) {
            $this->failure(ERROR_AMOUNT_TOO_MUCH);
            return true;
        }
        return false;
    }

    function begin_get($liveId)
    {
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($live->status != LIVE_STATUS_WAIT) {
            $this->failure(ERROR_LIVE_NOT_WAIT);
            return;
        }
        $this->statusDao->open($liveId);
        $ok = $this->liveDao->beginLive($liveId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed($ok);
    }

    function create_post()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $liveId = $this->liveDao->createLive($user->userId, $user->username . '的直播');
        if (!$liveId) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $live = $this->liveDao->getLive($liveId, $user);
        $this->succeed($live);
    }

    function update_post($liveId)
    {
        $keys = array(KEY_SUBJECT, KEY_COVER_URL, KEY_AMOUNT,
            KEY_DETAIL, KEY_PLAN_TS, KEY_PREVIEW_URL, KEY_SPEAKER_INTRO,
            KEY_NEED_PAY, KEY_NOTICE, KEY_SHARE_ICON);
        if ($this->checkIfNotAtLeastOneParam($this->post(), $keys)
        ) {
            return;
        }
        $data = $this->postParams($keys);
        if (isset($data[KEY_NEED_PAY])) {

        }

        if (isset($data[KEY_AMOUNT])) {
            $data[KEY_AMOUNT] = $this->toNumber($data[KEY_AMOUNT]);
            if (isset($data[KEY_NEED_PAY])) {
                $needPay = $this->toNumber($data[KEY_NEED_PAY]);
                if ($needPay) {
                    if ($this->checkIfAmountWrong($data[KEY_AMOUNT])) {
                        return;
                    }
                }
            }
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($user->userId != $live->ownerId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        $this->liveDao->update($liveId, $data);
        $this->succeed();
    }

    function updateTopic_post($liveId)
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_OP))) {
            return;
        }
        $topicId = $this->post(KEY_TOPIC_ID);
        $op = $this->post(KEY_OP);
        if ($op == OP_ADD) {
            if (!$topicId) {
                $this->failureOfParam(KEY_TOPIC_ID);
                return;
            }
        }
        if ($this->checkIfNotInArray($op, array(OP_ADD, OP_DEL))) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId, $user);
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        if ($op == OP_ADD) {
            $ok = $this->liveDao->updateTopic($liveId, $topicId);
            if (!$ok) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
            $this->succeed();
        } else if ($op == OP_DEL) {
            $this->liveDao->removeTopic($liveId);
            $this->succeed();
        } else {
            $this->failure(ERROR_PARAMETER_ILLEGAL);
        }
    }

    function list_get()
    {
        $skip = $this->skip();
        $limit = $this->limit();
        $user = $this->getSessionUser();
        $lives = $this->liveDao->getHomeLives($skip, $limit, $user);
        $this->succeed($lives);
    }

    function recommend_get()
    {
        $skip = $this->skip();
        $limit = $this->limit();
        $skipLiveId = $this->get(KEY_SKIP_LIVE_ID);
        if (!$skipLiveId) {
            $skipLiveId = 0;
        }
        $user = $this->getSessionUser();
        $lives = $this->liveDao->getRecommendLives($skip, $limit, $user, $skipLiveId);
        $this->succeed($lives);
    }

    function one_get($id)
    {
        $user = $this->getSessionUser();
        $live = $this->liveDao->getLive($id, $user);
        $this->succeed($live);
    }

    function alive_get($id)
    {
        $ok = $this->statusDao->alive($id);
        if (!$ok) {
            $this->failure(ERROR_ALIVE_FAIL);
            return;
        }
        $this->succeed($ok);
    }

    function end_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        if ($live->status != LIVE_STATUS_ON) {
            $this->failure(ERROR_LIVE_NOT_START);
            return;
        }
        $ok = $this->statusDao->setLiveTranscode($id);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed($ok);
    }

    function finish_get($id)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($id, $user);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        if ($live->status != LIVE_STATUS_ON) {
            $this->failure(ERROR_LIVE_NOT_START);
            return;
        }
        $url = $this->qiniuLive->getPlaybackUrl($live);
        if (!$url) {
            $this->failure(ERROR_PLAYBACK_FAIL);
            return;
        }
        $endOk = $this->liveDao->endLive($id);
        $ok = $this->videoDao->addVideoByLive($live);
        if (!$endOk || !$ok) {
            $this->failure(ERROR_SQL_WRONG);
        }
        $this->succeed();
    }

    function submitReview_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if (!$live->coverUrl || !trim($live->subject) || !trim($live->detail)) {
            $this->failure(ERROR_FIELDS_EMPTY);
            return;
        }
        if ($live->needPay && $this->checkIfAmountWrong($live->amount)) {
            return;
        }
        if (mb_strlen($live->detail) < 100) {
            $this->failure(ERROR_DETAIL_TOO_SHORT);
            return;
        }
        if (isTimeBeforeNow($live->planTs)) {
            $this->failure(ERROR_PLAN_TS_INVALID);
            return;
        }
        if ($live->status >= LIVE_STATUS_REVIEW) {
            $this->failure(ERROR_ALREADY_REVIEW);
            return;
        }
        if (mb_strlen($live->speakerIntro) < 50) {
            $this->failure(ERROR_SPEAKER_INTRO_TOO_SHORT);
            return;
        }
        $this->liveDao->setLiveReview($id);
        $this->succeed(true);
    }

    function publish_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $this->liveDao->setLivePrepare($id);
        $this->succeed();
    }

    function setWait_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        if ($live->status != LIVE_STATUS_ON) {
            $this->failure(ERROR_LIVE_NOT_ON);
            return;
        }
        $this->liveDao->setLivePrepare($id);
        $this->succeed();
    }

    function setReview_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        if ($live->status != LIVE_STATUS_WAIT) {
            $this->failure(ERROR_LIVE_NOT_WAIT);
            return;
        }
        $this->liveDao->setLiveReview($id);
        $this->succeed();
    }

    function lastPrepare_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->lastPrepareLive($user);
        if (!$live) {
            $this->failure(ERROR_CREATE_LIVE);
            return;
        }
        $this->succeed($live);
    }

    function attended_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $lvs = $this->liveDao->getAttendedLives($user);
        $this->succeed($lvs);
    }

    function my_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $lvs = $this->liveDao->getMyLives($user);
        $this->succeed($lvs);
    }

    function attendedUsers_get($liveId)
    {
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $skip = $this->skip();
        $limit = $this->limit();
        $users = $this->liveDao->getAttendedUsers($liveId, $skip, $limit);
        $this->succeed($users);
    }

    private function findUser($toUser, $users)
    {
        foreach ($users as $user) {
            if ($user->userId == $toUser->userId) {
                return $user;
            }
        }
        return null;
    }

    function notifyLiveStartRecommend_get($liveId)
    {
        if ($this->checkIfParamsNotExist($this->get(), array('relatedLiveId'))) {

        }
        $relatedLiveId = $this->get('relatedLiveId');
        $live = $this->liveDao->getLive($liveId);
        $realtedLive = $this->liveDao->getLive($relatedLiveId);

        $users = $this->liveDao->getAttendedUsers($liveId, 0, 1000000);
        $relatedUsers = $this->liveDao->getAttendedUsers($relatedLiveId, 0, 100000);
        $succeedCount = 0;
        foreach ($relatedUsers as $relatedUser) {
            $theUser = $this->findUser($relatedUser, $users);
            if ($theUser) {

            } else {
                $this->weChatPlatform->notifyUserByWeChat($relatedUser->userId, $live, false);
                $succeedCount++;
            }
        }
        logInfo("finished " . $succeedCount . " total " . count($relatedUsers));
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => count($relatedUsers)));
    }

    function notifyLiveStart_get($liveId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $oneHour = $this->toNumber($this->get('oneHour'));
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        $users = $this->liveDao->getAttendedUsers($liveId, 0, 1000000);
        $succeedCount = 0;
        foreach ($users as $user) {
            if ($user->notified == 0) {
                $charge = null;
                if ($user->orderNo) {
                    $charge = $this->chargeDao->getOneByOrderNo($user->orderNo);
                }
                if ($charge && $charge->channel == CHANNEL_WECHAT_APP) {
                    $ok = $this->weChatAppClient->notifyLiveStart($user->userId,
                        $charge->prepayId, $live, $oneHour);
                } else {
                    $ok = $this->weChatPlatform->notifyUserByWeChat($user->userId, $live, $oneHour);
                }

                if (!$ok) {
                    $ok = $this->sms->notifyLiveStart($user->userId, $live, $oneHour);
                }
                if ($ok) {
                    $this->attendanceDao->updateToNotified($user->userId, $live->liveId);
                    $succeedCount++;
                }
            } else {
            }
        }
        logInfo("finished " . $succeedCount . " total " . count($users));
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => count($users)));
    }

    function notifyVideo_get($liveId)
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
            if ($attendance->videoNotified == 0) {
                $attends = $this->attendanceDao->getAttendancesByUserId($attendance->userId, 0, 100);
                $ok = false;
                if (count($attends) <= 1) {
                    $ok = $this->weChatPlatform->notifyVideoByWeChat($attendance->userId, $live);
                    if (!$ok) {
                        $ok = $this->sms->notifyVideoReady($attendance->userId, $live);
                    }
                }
                if ($ok) {
                    $this->attendanceDao->updateToVideoNotified($attendance->userId, $live->liveId);
                    $succeedCount++;
                }
            }
        }
        logInfo('succeedCount:' . $succeedCount . ' total:' . $total);
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => $total));
    }

    function fixAttendanceCount_get()
    {
        $count = $this->liveDao->fixAttendanceCount();
        $this->succeed(array('succeedCount' => $count));
    }

    private function getBjfuUsers()
    {
        $all = file_get_contents(APPPATH . 'data/bjfudata.txt');
        $users = json_decode($all);
        $users = array_slice($users, 800, 0);
        return $users;
    }

    private function getTestUsers()
    {
        $user = new Stdclass();
        $user->username = '李智维';
        $user->mobilePhoneNumber = '18928980893';
        return array(
            $user
        );
    }

    function groupSend_get($liveId)
    {
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $bjfuUsers = $this->getBjfuUsers();
        logInfo("bjfu count:" . count($bjfuUsers));
        $thirdUsers = $bjfuUsers;
//        $thirdUsers = $this->getTestUsers();
        $succeedCount = 0;
        foreach ($thirdUsers as $thirdUser) {
            $ok = $this->sms->groupSend($thirdUser, $live);
            if ($ok) {
                $succeedCount++;
            }
        }
        $total = count($thirdUsers);
        logInfo("succeedCount: " . $succeedCount . ' total:' . $total);;
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => $total));
    }

    function import_get($liveId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $str = file_get_contents(APPPATH . 'data/iDev.json');
        $orders = json_decode($str);
        foreach ($orders as $order) {
            $id = $this->couponDao->addCoupon($order->phone, $liveId);
            if (!$id) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
        }
        $this->succeed();
    }

    function error_get($liveId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $ok = $this->liveDao->setLiveError($liveId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

}
