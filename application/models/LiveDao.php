<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/27/16
 * Time: 11:44 PM
 */
class LiveDao extends BaseDao
{

    public $leanCloud;
    public $userDao;
    public $couponDao;

    function __construct()
    {
        parent::__construct();
        $this->load->helper('string');
        $this->load->helper('array');
        $this->load->library('LeanCloud');
        $this->leanCloud = new LeanCloud();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
        $this->load->model(CouponDao::class);
        $this->couponDao = new CouponDao();
    }

    private function genLiveKey()
    {
        return random_string('alnum', 8);
    }

    function createLive($ownerId, $subject)
    {
        $result = $this->leanCloud->createConversation($subject, $ownerId);
        if ($result == null) {
            return null;
        }
        $id = $this->createLiveRecord($ownerId, $subject, $result);
        return $id;
    }

    function createLiveRecord($ownerId, $subject, $conversationId)
    {
        $key = $this->genLiveKey();
        $data = array(
            KEY_OWNER_ID => $ownerId,
            KEY_SUBJECT => $subject,
            KEY_RTMP_KEY => $key,
            KEY_STATUS => LIVE_STATUS_PREPARE,
            KEY_MAX_PEOPLE => 1000,
            KEY_CONVERSATION_ID => $conversationId
        );
        $this->db->insert(TABLE_LIVES, $data);
        return $this->db->insert_id();
    }

    function getLive($id, $user = null)
    {
        $lives = $this->getLives(array($id), $user);
        if (count($lives) > 0) {
            return $lives[0];
        }
        return null;
    }

    function getHomeLives($skip, $limit, $user)
    {
        $sql = "SELECT liveId FROM lives
                WHERE status>=?
                ORDER BY created DESC
                limit $limit offset $skip";
        $lives = $this->db->query($sql, array(LIVE_STATUS_WAIT))->result();
        $ids = $this->extractLiveIds($lives);
        return $this->getLivesWithoutDetail($ids, $user, true);
    }

    private function extractLiveIds($lives)
    {
        $ids = array();
        foreach ($lives as $live) {
            array_push($ids, $live->liveId);
        }
        return $ids;
    }

    private function getLivesWithoutDetail($liveIds, $user, $sortByPlanTs = false)
    {
        $lvs = $this->getLives($liveIds, $user, $sortByPlanTs);
        foreach ($lvs as $lv) {
            unset($lv->detail);
            unset($lv->speakerIntro);
        }
        return $lvs;
    }

    private function getLives($liveIds, $user, $sortByPlanTs = false)
    {
        $userId = -1;
        if ($user) {
            $userId = $user->userId;
        }
        if (count($liveIds) == 0) {
            return array();
        }
        $sortField = null;
        if ($sortByPlanTs) {
            $sortField = "l.planTs";
        } else {
            $sortField = "l.created";
        }
        $fields = $this->livePublicFields('l');
        $userFields = $this->userPublicFields('u', true);
        $sql = "select $fields, $userFields,a.attendanceId,s.shareId from lives as l
                left join users as u on u.userId=l.ownerId
                left join attendances as a on a.liveId = l.liveId and a.userId = $userId
                left join shares as s on s.liveId = l.liveId and s.userId= $userId
                where l.liveId in (" . implode(', ', $liveIds) . ")
                order by $sortField desc";
        $lives = $this->db->query($sql)->result();
        $this->assembleLives($lives, $user);
        return $lives;
    }

    function getLiveByRtmpKey($key)
    {
        return $this->getOneFromTable(TABLE_LIVES, KEY_RTMP_KEY, $key);
    }

    private function electHlsServer()
    {
        return random_element(array('live1.quzhiboapp.com', 'live2.quzhiboapp.com',
            'live3.quzhiboapp.com'));
    }

    private function electFlvServer()
    {
        return random_element(array('flv1.quzhiboapp.com:8080', 'flv2.quzhiboapp.com:8080'));
    }

    private function electRtmpServer()
    {
        return random_element(array('rtmp1.quzhiboapp.com'));
    }

    private function calAmount($live, $user)
    {
        $origin = $live->amount;
        $shareId = $live->shareId;
        if (!$user) {
            return $origin;
        }
        $user = $this->userDao->findUserById($user->userId);
        $have = $this->couponDao->haveCoupon($user->mobilePhoneNumber, $live->liveId);
        if ($have) {
            $this->couponDao->updateCouponUserId($user->mobilePhoneNumber, $live->liveId, $user->userId);
            $origin = 100;
        }
        if ($shareId) {
            $amount = $origin - 100;
            if ($amount <= 0) {
                $amount = 1;
            }
            return $amount;
        } else {
            return $origin;
        }
    }

    private function assembleLives($lives, $user)
    {
        foreach ($lives as $live) {
            $us = $this->prefixFields($this->userPublicRawFields(), 'u');
            $live->owner = extractFields($live, $us, 'u');

            if (!$live->attendanceId && $user && $user->userId != $live->ownerId) {
                // 没参加或非创建者
                $live->canJoin = false;
                unset($live->rtmpKey);
            } else {
                $hlsHost = $this->electHlsServer();
                $rtmpHost = $this->electRtmpServer();
                $flvHost = $this->electFlvServer();
                if ($user && $user->userId == $live->ownerId) {
                    $live->pushUrl = 'rtmp://cheer.quzhiboapp.com/live/' . $live->rtmpKey;
                    $live->foreignPushUrl = 'rtmp://vnet.quzhiboapp.com:31935/live/' . $live->rtmpKey;
                }
                $live->videoUrl = 'http://video.quzhiboapp.com/' . $live->rtmpKey . '.mp4';
                $live->rtmpUrl = 'rtmp://' . $rtmpHost . '/live/' . $live->rtmpKey;
                $live->hlsUrl = 'http://' . $hlsHost . '/live/' . $live->rtmpKey . '.m3u8';
                $live->flvUrl = 'http://' . $flvHost . '/live/' . $live->rtmpKey . '.flv';
                $live->canJoin = true;
            }
            $live->realAmount = $this->calAmount($live, $user);
        }
    }

    function update($id, $data)
    {
        $this->db->where(KEY_LIVE_ID, $id);
        return $this->db->update(TABLE_LIVES, $data);
    }

    function endLive($id)
    {
        return $this->update($id, array(
            KEY_END_TS => date('Y-m-d H:i:s'),
            KEY_STATUS => LIVE_STATUS_OFF
        ));
    }

    function beginLive($id)
    {
        return $this->update($id, array(
            KEY_BEGIN_TS => date('Y-m-d H:i:s'),
            KEY_STATUS => LIVE_STATUS_ON
        ));
    }

    function resumeLive($id)
    {
        return $this->setLiveStatus($id, LIVE_STATUS_ON);
    }

    private function setLiveStatus($id, $status)
    {
        return $this->update($id, array(
            KEY_STATUS => $status
        ));
    }

    function setLiveReview($id)
    {
        return $this->setLiveStatus($id, LIVE_STATUS_REVIEW);
    }

    function setLivePrepare($id)
    {
        return $this->setLiveStatus($id, LIVE_STATUS_WAIT);
    }

    function incrementAttendanceCount($liveId)
    {
        $sql = "UPDATE lives SET attendanceCount = attendanceCount+1 WHERE liveId=?";
        $binds = array($liveId);
        return $this->db->query($sql, $binds);
    }

    function lastPrepareLive($user)
    {
        $sql = "SELECT liveId FROM lives WHERE ownerId=? AND status<=?";
        $binds = array($user->userId, LIVE_STATUS_ON);
        $live = $this->db->query($sql, $binds)->row();
        if ($live) {
            return $this->getLive($live->liveId);
        } else {
            $liveId = $this->createLive($user->userId, $user->username . '的直播');
            if (!$liveId) {
                return null;
            } else {
                return $this->getLive($liveId);
            }
        }
    }

    function getAttendedLives($user)
    {
        $sql = "SELECT a.liveId FROM attendances AS a WHERE a.userId=?";
        $binds = array($user->userId);
        $lives = $this->db->query($sql, $binds)->result();
        $ids = $this->extractLiveIds($lives);
        return $this->getLivesWithoutDetail($ids, $user, true);
    }

    function getMyLives($user)
    {
        $sql = "SELECT liveId FROM lives AS l WHERE l.ownerId=?  ORDER BY created DESC";
        $binds = array($user->userId);
        $lives = $this->db->query($sql, $binds)->result();
        $ids = $this->extractLiveIds($lives);
        return $this->getLivesWithoutDetail($ids, $user);
    }

    function getAttendedUsers($liveId, $skip, $limit)
    {
        $fields = $this->userPublicFields('u');
        $attendanceFields = $this->attendancePublicFields('a');
        $sql = "SELECT $fields,$attendanceFields from attendances as a
               left join users as u on u.userId = a.userId
               where a.liveId=? order by a.created desc
               limit $limit OFFSET $skip";
        $binds = array($liveId);
        $users = $this->db->query($sql, $binds)->result();
        return $users;
    }

    function fixAttendanceCount()
    {
        $sql = "SELECT liveId FROM lives";
        $lives = $this->db->query($sql)->result();
        $count = 0;
        foreach ($lives as $live) {
            $liveId = $live->liveId;
            $updateSql = "UPDATE lives SET attendanceCount=? WHERE liveId=?";
            $users = $this->getAttendedUsers($liveId, 0, 100000);
            $binds = array(count($users), $liveId);
            $ok = $this->db->query($updateSql, $binds);
            if ($ok) {
                $count++;
            }
        }
        return $count;
    }

}
