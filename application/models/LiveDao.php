<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/27/16
 * Time: 11:44 PM
 */
class LiveDao extends BaseDao
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('string');
    }

    private function genLiveKey()
    {
        return random_string('alnum', 8);
    }

    function createLive($ownerId, $subject)
    {
        $key = $this->genLiveKey();
        $data = array(
            KEY_OWNER_ID => $ownerId,
            KEY_SUBJECT => $subject,
            KEY_RTMP_KEY => $key,
            KEY_STATUS => LIVE_STATUS_PREPARE,
        );
        $this->db->insert(TABLE_LIVES, $data);
        return $this->db->insert_id();
    }

    function getLive($id)
    {
        $lives = $this->getLives(KEY_LIVE_ID, $id, 0, 1);
        if (count($lives) > 0) {
            return $lives[0];
        }
        return null;
    }

    function getLivingLives($skip, $limit)
    {
        $lives = $this->getLives(KEY_STATUS, LIVE_STATUS_ON, $skip, $limit);
        return $lives;
    }

    private function getLives($field, $value, $skip, $limit)
    {
        $fields = $this->livePublicFields('l');
        $userFields = $this->userPublicFields('u', true);
        $sql = "select $fields, $userFields from lives as l
                left join users as u on u.userId=l.ownerId
                where l.$field = ?
                limit $limit offset $skip";
        $lives = $this->db->query($sql, array($value))->result();
        $this->assembleLives($lives);
        return $lives;
    }

    private function assembleLives($lives)
    {
        foreach ($lives as $live) {
            $us = $this->prefixFields($this->userPublicRawFields(), 'u');
            $live->owner = extractFields($live, $us, 'u');
            $live->rtmpUrl = "rtmp://hotimg.cn/live/" . $live->rtmpKey;
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

    function publishLive($id)
    {
        return $this->update($id, array(
            KEY_STATUS => LIVE_STATUS_WAIT
        ));
    }

    function incrementAttendanceCount()
    {
        $sql = "UPDATE lives SET attendanceCount = attendanceCount+1";
        return $this->db->query($sql);
    }

    function lastPrepareLive($user)
    {
        $sql = "SELECT liveId FROM lives WHERE ownerId=? AND status=?";
        $binds = array($user->userId, LIVE_STATUS_PREPARE);
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

}
