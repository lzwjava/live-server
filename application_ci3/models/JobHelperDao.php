<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 2/15/17
 * Time: 5:50 PM
 */
class JobHelperDao extends BaseDao
{
    public $liveDao;
    public $attendanceDao;
    public $chargeDao;
    public $weChatAppClient;
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
        $this->load->library(WeChatAppClient::class);
        $this->weChatAppClient = new WeChatAppClient();
        $this->load->library(WeChatPlatform::class);
        $this->weChatPlatform = new WeChatPlatform();
    }

    function notifyLiveStartWithType($liveId, $type)
    {
        $users = $this->liveDao->getAttendedUsers($liveId, 0, 1000000);
        $succeedCount = 0;
        $live = $this->liveDao->getLive($liveId);
        foreach ($users as $user) {
            if ($type == 0) {
                if (!$user->firstNotified) {
                    $ok = $this->notifyLiveStart($user, $live);
                    if ($ok) {
                        $this->attendanceDao->updateToFirstNotified($user->userId, $live->liveId);
                        $succeedCount++;
                    }
                }
            } else if ($type == 1) {
                if (!$user->preNotified) {
                    $ok = $this->notifyLiveStart($user, $live);
                    if ($ok) {
                        $this->attendanceDao->updateToPreNotified($user->userId, $live->liveId);
                        $succeedCount++;
                    }
                }
            } else if ($type == 2) {
                if (!$user->notified) {
                    $ok = $this->notifyLiveStart($user, $live);
                    if ($ok) {
                        $this->attendanceDao->updateToNotified($user->userId, $live->liveId);
                        $succeedCount++;
                    }
                }
            }
        }
        return array('succeedCount' => $succeedCount, 'total' => count($users));
    }

    private function notifyLiveStart($user, $live)
    {
        $charge = null;
        if ($user->orderNo) {
            $charge = $this->chargeDao->getOneByOrderNo($user->orderNo);
        }
        $ok = null;
        if ($charge && $charge->channel == CHANNEL_WECHAT_APP) {
            $ok = $this->weChatAppClient->notifyLiveStart($user->userId,
                $charge->prepayId, $live);
        } else {
            $ok = $this->weChatPlatform->notifyUserByWeChat($user->userId, $live);
        }
        return $ok;
    }

}