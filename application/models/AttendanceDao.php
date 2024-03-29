<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:03
 */
class AttendanceDao extends BaseDao
{
    /** @var LiveDao */
    public $liveDao;
    /** @var WeChatPlatform */
    public $weChatPlatform;
    /* @var $userDao */
    public $userDao;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->library(WeChatPlatform::class);
        $this->weChatPlatform = new WeChatPlatform();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    private function addAttendance($userId, $liveId, $orderNo, $fromUserId)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_LIVE_ID => $liveId
        );
        if ($orderNo) {
            $data[KEY_ORDER_NO] = $orderNo;
        }
        if ($fromUserId) {
            $data[KEY_FROM_USER_ID] = $fromUserId;
        }
        $this->db->insert(TABLE_ATTENDANCES, $data);
        return $this->db->insert_id();
    }

    function addAttendanceAndIncreaseCount($userId, $liveId, $orderNo, $fromUserId)
    {
        $id = $this->addAttendance($userId, $liveId, $orderNo, $fromUserId);
        if (!$id) {
            return null;
        }
        $ok = $this->incrementAttendanceCount($liveId);
        if (!$ok) {
            return null;
        }

        //用户报名成功后  通过微信通知用户
        $live = $this->liveDao->getLive($liveId);
        $this->weChatPlatform->notifyUserAttendanceSuccessByWeChat($userId, $live);
        return $id;
    }

    function incrementAttendanceCount($liveId)
    {
        $sql = "UPDATE lives SET attendanceCount = attendanceCount+1 WHERE liveId=?";
        $binds = array($liveId);
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

    function getAttendance($userId, $liveId)
    {
        $fields = $this->attendancePublicFields();
        $sql = "select $fields from attendances where userId=? and liveId=?";
        $binds = array($userId, $liveId);
        return $this->db->query($sql, $binds)->row();
    }

    function getAttendanceById($attendanceId)
    {
        $fields = $this->attendancePublicFields();
        $sql = "select $fields from attendances where attendanceId=?";
        $binds = array($attendanceId);
        return $this->db->query($sql, $binds)->row();
    }

    private function update($attendanceId, $data)
    {
        $this->db->where(KEY_ATTENDANCE_ID, $attendanceId);
        return $this->db->update(TABLE_ATTENDANCES, $data);
    }

    function getAttendancesByUserId($userId, $skip, $limit)
    {
        return $this->getAttendances(KEY_USER_ID, $userId, $skip, $limit);
    }

    function getAttendancesByLiveId($liveId, $skip, $limit)
    {
        return $this->getAttendances(KEY_LIVE_ID, $liveId, $skip, $limit);
    }

    function queryInviteList($liveId, $skip, $limit)
    {
        $userFields = $this->userPublicFields('u');
        $inviteIncomeRate = INVITE_INCOME_RATE;
        $sql = "SELECT $userFields,count(attendanceId) as inviteCount,
                round(sum(c.amount) * $inviteIncomeRate) as inviteIncome
                FROM attendances AS a
                LEFT JOIN charges AS c ON c.orderNo=a.orderNo
                LEFT JOIN users AS u ON u.userId=a.fromUserId
                WHERE liveId=? and fromUserId is not null GROUP BY fromUserId
                order by inviteIncome desc, inviteCount desc
                limit $limit offset $skip";
        $binds = array($liveId);
        $inviteUsers = $this->db->query($sql, $binds)->result();
        $this->userDao->fixUsersAvatars($inviteUsers);
        return $inviteUsers;
    }

    private function getAttendances($field, $value, $skip = 0, $limit = 100)
    {
        $fields = $this->attendancePublicFields('a');
        $liveFields = $this->livePublicFields('l', true);
        $userFields = $this->userPublicFields('u', true);
        $sql = "select $fields,$liveFields,$userFields
                from attendances as a
                left join lives as l USING(liveId)
                left join users as u on u.userId=a.userId
                where a.$field=?
                limit $limit offset $skip";
        $binds = array($value);
        $attendances = $this->db->query($sql, $binds)->result();
        $this->handleAttendances($attendances);
        return $attendances;
    }

    protected function handleAttendances($attendances)
    {
        foreach ($attendances as $attendance) {
            $ls = $this->prefixFields($this->liveFields(), 'l');
            $attendance->live = extractFields($attendance, $ls, 'l');
            $us = $this->prefixFields($this->userPublicRawFields(), 'u');
            $attendance->user = extractFields($attendance, $us, 'u');
        }
    }

    function updateToNotified($userId, $liveId)
    {
        $data = array(
            KEY_NOTIFIED => 1
        );
        return $this->updateAttendance($userId, $liveId, $data);
    }

    private function updateAttendance($userId, $liveId, $data)
    {
        $this->db->where(KEY_USER_ID, $userId);
        $this->db->where(KEY_LIVE_ID, $liveId);;
        $this->db->update(TABLE_ATTENDANCES, $data);
        return $this->db->affected_rows() > 0;
    }

    function updateToPreNotified($userId, $liveId)
    {
        $data = array(
            KEY_PRE_NOTIFIED => 1
        );
        return $this->updateAttendance($userId, $liveId, $data);
    }

    function updateToVideoNotified($userId, $liveId)
    {
        $data = array(
            KEY_VIDEO_NOTIFIED => 1
        );
        return $this->updateAttendance($userId, $liveId, $data);
    }

    function updateToFirstNotified($userId, $liveId)
    {
        $data = array(
            KEY_FIRST_NOTIFIED => 1
        );
        return $this->updateAttendance($userId, $liveId, $data);
    }

}
