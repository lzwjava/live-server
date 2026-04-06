<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/28/16
 * Time: 4:24 AM
 */
class StaffDao extends BaseDao
{
    protected $table = 'staffs';

    function addStaff($userId)
    {
        $data = array(
            KEY_USER_ID => $userId
        );
        $this->db->table(TABLE_STAFFS)->insert($data);
        return $this->db->insertID();
    }

    private function getStaffs()
    {
        $sql = "SELECT userId FROM staffs";
        return $this->db->query($sql)->getResult();
    }

    function isStaff($userId)
    {
        return in_array($userId, $this->getStaffIds());
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

// Namespace bridge: allow App\Libraries\StaffDao → App\Models\StaffDao
class_alias('App\Models\StaffDao', 'App\Libraries\StaffDao');
