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
    public $staffDao;
    public $topicDao;
    public $qiniuLive;

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
        $this->load->model(StaffDao::class);
        $this->staffDao = new StaffDao();
        $this->load->model(TopicDao::class);
        $this->topicDao = new TopicDao();
        $this->load->library(QiniuLive::class);
        $this->qiniuLive = new QiniuLive();
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
            KEY_MAX_PEOPLE => LIVE_INIT_MAX_PEOPLE,
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
                WHERE status>=? and status != ?
                ORDER BY planTs DESC
                limit $limit offset $skip";
        $lives = $this->db->query($sql, array(LIVE_STATUS_WAIT, LIVE_STATUS_ERROR))->result();
        $ids = $this->extractLiveIds($lives);
        return $this->getLivesWithoutDetail($ids, $user, true);
    }

    function getRecommendLives($skip, $limit, $user, $skipLiveId)
    {
        $sql = "SELECT liveId FROM lives
                WHERE status>=? and status!=? and liveId != ?
                ORDER BY planTs DESC
                limit $limit offset $skip";
        $lives = $this->db->query($sql, array(LIVE_STATUS_WAIT,
            LIVE_STATUS_ERROR, $skipLiveId))->result();
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
        $topicFields = $this->topicDao->topicPublicFields('t', true);
        $sql = "select $fields, $userFields,a.attendanceId,s.shareId, $topicFields from lives as l
                left join users as u on u.userId=l.ownerId
                left join attendances as a on a.liveId = l.liveId and a.userId = $userId
                left join shares as s on s.liveId = l.liveId and s.userId= $userId
                left join topics as t on t.topicId = l.topicId
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

    function getRawLive($liveId)
    {
        return $this->getOneFromTable(TABLE_LIVES, KEY_LIVE_ID, $liveId);
    }

    function getRawLivesByStatus($status)
    {
        return $this->getListFromTable(TABLE_LIVES, KEY_STATUS, $status);
    }

    private function electHlsServer()
    {
//        return 'hls-cdn.quzhiboapp.com';
//        return 'live-cdn.quzhiboapp.com';
        return random_element(array('hls-xycdn.quzhiboapp.com/live',
            'live-cdn.quzhiboapp.com/live', 'pili-live-hls.quzhiboapp.com/qulive'));
    }

    private function electFlvServer()
    {
        return random_element(array('live-cdn.quzhiboapp.com/live',
            'pili-live-hls.quzhiboapp.com/qulive'));
//        return 'flv-xycdn.quzhiboapp.com';
//        return 'cheer.quzhiboapp.com';
    }

    private function electRtmpServer()
    {
//        return random_element(array('rtmp1.quzhiboapp.com'));
//        return 'live-cdn.quzhiboapp.com';
        return 'xycdn.quzhiboapp.com/live';
    }

    private function calAmount($live, $user, $staffIds)
    {
        $origin = $live->amount;
        $shareId = $live->shareId;
        if (!$user) {
            return $origin;
        }
        if (in_array($live->liveId, array(180))) {
            $user = $this->userDao->findUserById($user->userId);
            $have = $this->couponDao->haveCoupon($user->mobilePhoneNumber, $live->liveId);
            if ($have) {
                $this->couponDao->updateCouponUserId($user->mobilePhoneNumber, $live->liveId, $user->userId);
                $origin = 100;
            }
        }
        if (in_array($user->userId, $staffIds)) {
            $origin = 1;
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
        $staffIds = $this->staffDao->getStaffIds();
        $staffIds = array();
        foreach ($lives as $live) {
            $us = $this->prefixFields($this->userPublicRawFields(), 'u');
            $live->owner = extractFields($live, $us, 'u');
            $topicFields = $this->prefixFields($this->topicDao->topicFields(), 't');
            $live->topic = extractFields($live, $topicFields, 't');
            if ($live->attendanceId || ($user && $user->userId == $live->ownerId)) {
                // 参加了或是创建者
                $hlsHostLive = $this->electHlsServer();
                $rtmpHostLive = $this->electRtmpServer();
                $flvHostLive = $this->electFlvServer();
                if ($user && $user->userId == $live->ownerId) {
//                    $live->pushUrl = 'rtmp://cheer.quzhiboapp.com/live/' . $live->rtmpKey
//                        . '?vhost=live-cdn.quzhiboapp.com';
                    $live->pushUrl = 'rtmp://cheer.quzhiboapp.com/live/' . $live->rtmpKey;
                    $live->foreignPushUrl = 'rtmp://vnet.quzhiboapp.com:31935/live/' . $live->rtmpKey;
                }
                $live->videoUrl = VIDEO_HOST_URL . $live->rtmpKey . '.mp4';
                $live->rtmpUrl = 'rtmp://' . $rtmpHostLive . '/' . $live->rtmpKey;
                $live->hlsUrl = 'http://' . $hlsHostLive . '/' . $live->rtmpKey . '.m3u8';
                $live->flvUrl = 'http://' . $flvHostLive . '/' . $live->rtmpKey . '.flv';
                $live->canJoin = true;
            } else {
                $live->canJoin = false;
                unset($live->rtmpKey);
                unset($live->notice);
            }
            $live->realAmount = $this->calAmount($live, $user, $staffIds);
        }
    }

    function update($id, $data)
    {
        $this->db->where(KEY_LIVE_ID, $id);
        $this->db->update(TABLE_LIVES, $data);
        return $this->db->affected_rows() > 0;
    }

    function updateTopic($liveId, $topicId)
    {
        $data = array(KEY_TOPIC_ID => $topicId);
        return $this->update($liveId, $data);
    }

    function removeTopic($liveId)
    {
        $this->db->set(KEY_TOPIC_ID, 'NULL', false);
        $this->db->where(KEY_LIVE_ID, $liveId);
        $this->db->update(TABLE_LIVES);
        return $this->db->affected_rows() > 0;
    }

    function setLiveTranscode($id)
    {
        return $this->update($id, array(
            KEY_END_TS => date('Y-m-d H:i:s'),
            KEY_STATUS => LIVE_STATUS_TRANSCODE
        ));
    }

    function endLive($id)
    {
        return $this->update($id, array(
            KEY_STATUS => LIVE_STATUS_OFF
        ));
    }


    function setLiveError($id)
    {
        return $this->update($id, array(
            KEY_STATUS => LIVE_STATUS_ERROR
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

    function haveWaitLive($userId)
    {
        $sql = "SELECT count(*) AS cnt FROM lives WHERE status >= ?
                AND status < ? AND ownerId=?";
        $binds = array(LIVE_STATUS_WAIT, LIVE_STATUS_OFF, $userId);
        $row = $this->db->query($sql, $binds)->row();
        return $row->cnt > 0;
    }

}
