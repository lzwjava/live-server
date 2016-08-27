<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/3/2
 * Time: 上午1:03
 */
class AttendanceDao extends BaseDao
{

    function addAttendance($userId, $liveId, $orderNo)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_LIVE_ID => $liveId,
            KEY_ORDER_NO => $orderNo
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

}
