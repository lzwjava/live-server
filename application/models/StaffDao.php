<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/28/16
 * Time: 4:24 AM
 */
class StaffDao extends BaseDao
{
    function addStaff($userId)
    {
        $data = array(
            KEY_USER_ID => $userId
        );
        $this->db->insert(TABLE_STAFFS, $data);
        return $this->db->insert_id();
    }

    private function getStaffs()
    {
        $sql = "SELECT userId FROM staffs";
        return $this->db->query($sql)->result();
    }

    function getStaffIds()
    {
        $staffs = $this->getStaffs();
        $staffIds = array();
        foreach ($staffs as $staff) {
            array_push($staffIds, $staff->userId);
        }
        return $staffIds;
    }
}
