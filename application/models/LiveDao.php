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

    function __construct()
    {
        parent::__construct();
        $this->load->helper('string');
        $this->load->helper('array');
        $this->load->library('LeanCloud');
        $this->leanCloud = new LeanCloud();
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
            KEY_MAX_PEOPLE => 300,
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
                WHERE status>=? ORDER BY created
                DESC limit $limit offset $skip";
        $lives = $this->db->query($sql, array(LIVE_STATUS_WAIT))->result();
        $ids = $this->extractLiveIds($lives);
        return $this->getLives($ids, $user);
    }

    private function extractLiveIds($lives)
    {
        $ids = array();
        foreach ($lives as $live) {
            array_push($ids, $live->liveId);
        }
        return $ids;
    }

    private function getLives($liveIds, $user)
    {
        $userId = -1;
        if ($user) {
            $userId = $user->userId;
        }
        if (count($liveIds) == 0) {
            return array();
        }
        $fields = $this->livePublicFields('l');
        $userFields = $this->userPublicFields('u', true);
        $sql = "select $fields, $userFields,a.attendanceId from lives as l
                left join users as u on u.userId=l.ownerId
                left join attendances as a on a.liveId = l.liveId and a.userId = $userId
                where l.liveId in (" . implode(', ', $liveIds) . ")";
        $lives = $this->db->query($sql)->result();
        $this->assembleLives($lives, $userId);
        return $lives;
    }

    private function electRtmpServer()
    {
        $serverIps = array('cheer.quzhiboapp.com');
        $serverIp = random_element($serverIps);
        return $serverIp;
    }

    private function assembleLives($lives, $userId)
    {
        foreach ($lives as $live) {
            $us = $this->prefixFields($this->userPublicRawFields(), 'u');
            $live->owner = extractFields($live, $us, 'u');
            $serverHost = $this->electRtmpServer();
            $live->rtmpUrl = 'rtmp://' . $serverHost . '/live/' . $live->rtmpKey;
            $live->hlsUrl = 'http://' . $serverHost . '/live/' . $live->rtmpKey . '_ff.m3u8';
            if (!$live->attendanceId && $userId != $live->ownerId) {
                // 没参加或非创建者
                unset($live->rtmpUrl);
                unset($live->rtmpKey);
                unset($live->hlsUrl);
                $live->canJoin = false;
            } else {
                $live->canJoin = true;
            }
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

    function incrementAttendanceCount()
    {
        $sql = "UPDATE lives SET attendanceCount = attendanceCount+1";
        return $this->db->query($sql);
    }

    function lastPrepareLive($user)
    {
        $sql = "SELECT liveId FROM lives WHERE ownerId=? AND status<=?";
        $binds = array($user->userId, LIVE_STATUS_WAIT);
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
        $sql = "SELECT a.liveId FROM attendances AS a WHERE a.userId=? ORDER BY created DESC";
        $binds = array($user->userId);
        $lives = $this->db->query($sql, $binds)->result();
        $ids = $this->extractLiveIds($lives);
        return $this->getLives($ids, $user);
    }

    function getMyLives($user)
    {
        $sql = "SELECT liveId FROM lives AS l WHERE l.ownerId=?  ORDER BY created DESC";
        $binds = array($user->userId);
        $lives = $this->db->query($sql, $binds)->result();
        $ids = $this->extractLiveIds($lives);
        return $this->getLives($ids, $user);
    }

    function getAttendedUsers($liveId)
    {
        $fields = $this->userPublicFields('u');
        $sql = "SELECT $fields from users as u where u.userId in
                (SELECT userId FROM attendances AS a WHERE a.liveId=? ORDER BY created DESC)";
        $binds = array($liveId);
        $users = $this->db->query($sql, $binds)->result();
        return $users;
    }

}
