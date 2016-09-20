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

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(StatusDao::class);
        $this->statusDao = new StatusDao();
        $this->load->library(Sms::class);
        $this->sms = new Sms();
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
        $ok = $this->statusDao->open($liveId);
        if (!$ok) {
            $this->failure(ERROR_REDIS_WRONG);
            return;
        }
        $ok = $this->liveDao->beginLive($liveId);
        $this->succeed($ok);
    }

    function update_post($liveId)
    {
        $keys = array(KEY_SUBJECT, KEY_COVER_URL, KEY_AMOUNT, KEY_DETAIL, KEY_PLAN_TS);
        if ($this->checkIfNotAtLeastOneParam($this->post(), $keys)
        ) {
            return;
        }
        $data = $this->postParams($keys);
        if (isset($data[KEY_AMOUNT])) {
            $data[KEY_AMOUNT] = $this->toNumber($data[KEY_AMOUNT]);
            if ($this->checkIfAmountWrong($data[KEY_AMOUNT])) {
                return;
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
        $ok = $this->liveDao->update($liveId, $data);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
        }
        $live = $this->liveDao->getLive($liveId);
        $this->succeed($live);
    }

    function list_get()
    {
        $skip = $this->skip();
        $limit = $this->limit();
        $user = $this->getSessionUser();
        $lives = $this->liveDao->getHomeLives($skip, $limit, $user);
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
        $ok = $this->statusDao->endLive($id);
        $this->succeed($ok);
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
        if ($this->checkIfAmountWrong($live->amount)) {
            return;
        }
        if (mb_strlen($live->detail) < 300) {
            $this->failure(ERROR_DETAIL_TOO_SHORT);
            return;
        }
        if (isTimeBeforeNow($live->planTs)) {
            $this->failure(ERROR_PLAN_TS_INVALID);
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
        $users = $this->liveDao->getAttendedUsers($liveId);
        $this->succeed($users);
    }

    function notifyLiveStart_get($liveId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($live->ownerId != $user->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        $users = $this->liveDao->getAttendedUsers($liveId);
        $succeedCount = 0;
        foreach ($users as $user) {
            usleep(1000 * 100);
            logInfo("notify user " . $user->userId);
            $ok = $this->sms->notifyLiveStart($user, $live);
            if ($ok) {
                $succeedCount++;
            }
        }
        $this->succeed(array('succeedCount' => $succeedCount, 'total' => count($users)));
    }

}
