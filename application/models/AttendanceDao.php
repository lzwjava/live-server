<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:03
 */
class AttendanceDao extends BaseDao
{
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    function addAttendance($userId, $liveId, $chargeId)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_LIVE_ID => $liveId,
            KEY_CHARGE_ID => $chargeId
        );
        $this->db->insert(TABLE_ATTENDANCES, $data);
        return $this->db->insert_id();
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

    private function getAttendances($field, $value, $skip = 0, $limit = 100)
    {
        $fields = $this->attendancePublicFields('a');
        $liveFields = $this->livePublicFields('l', true);
        $userFields = $this->userDao->publicFields('u', true);
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
            $attendance->event = extractFields($attendance, $ls, 'l');
            $us = $this->prefixFields($this->userDao->publicRawFields(), 'u');
            $attendance->user = extractFields($attendance, $us, 'u');
        }
    }

}