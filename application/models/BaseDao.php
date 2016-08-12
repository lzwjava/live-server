<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/2
 * Time: 上午12:32
 */
class BaseDao extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->db->query("SET NAMES UTF8MB4");
    }

    protected function mergeFields($fields, $tableName = null, $alias = false)
    {
        if ($tableName) {
            foreach ($fields as &$field) {
                $aliasPart = $tableName . $field;
                $field = $tableName . '.' . $field;
                if ($alias) {
                    $field .= ' as ' . $aliasPart;
                }
            }
        }
        return implode($fields, ',');
    }

    protected function getOneFromTable($table, $field, $value, $fields = "*")
    {
        $sql = "SELECT $fields FROM $table WHERE $field=?";
        $array = $value;
        $result = $this->db->query($sql, $array)->row();
        return $result;
    }

    protected function getListFromTable($table, $field, $value, $fields = "*", $orderBy = null,
                                        $skip = 0, $limit = 100)
    {
        $order = '';
        if ($orderBy) {
            $order = ' order by ' . $orderBy . ' ';
        }
        $sql = "SELECT $fields FROM $table WHERE $field=? $order limit $limit offset $skip";
        $values[] = $value;
        $result = $this->db->query($sql, $values)->result();
        return $result;
    }

    protected function countRows($table, $field, $value)
    {
        $sql = "SELECT count(*) AS cnt FROM $table WHERE $field=?";
        $array[] = $value;
        $result = $this->db->query($sql, $array)->row();
        return $result->cnt;
    }


    protected function extractFields($object, $fields)
    {
        $newObj = new StdClass();
        foreach ($fields as $field) {
            $newObj->$field = $object->$field;
            unset($object->$field);
        }
        return $newObj;
    }

    protected function prefixFields($fields, $prefix)
    {
        foreach ($fields as &$field) {
            $field = $prefix . $field;
        }
        return $fields;
    }

    protected function attendanceFields()
    {
        return array(KEY_ATTENDANCE_ID, KEY_USER_ID, KEY_LIVE_ID, KEY_CHARGE_ID, KEY_CREATED);
    }

    protected function attendancePublicFields($prefix = TABLE_ATTENDANCES, $alias = false)
    {
        return $this->mergeFields($this->attendanceFields(), $prefix, $alias);
    }

    protected function liveFields()
    {
        return array(KEY_LIVE_ID, KEY_SUBJECT, KEY_RTMP_KEY, KEY_STATUS, KEY_ATTENDANCE_COUNT, KEY_PLAN_TS,
            KEY_COVER_URL, KEY_AMOUNT, KEY_BEGIN_TS, KEY_END_TS, KEY_OWNER_ID, KEY_DETAIL);
    }

    protected function livePublicFields($prefix = TABLE_LIVES, $alias = false)
    {
        return $this->mergeFields($this->liveFields(), $prefix, $alias);
    }

    function userPublicRawFields()
    {
        return array(KEY_USER_ID, KEY_AVATAR_URL, KEY_USERNAME);
    }

    function userPublicFields($prefix = TABLE_USERS, $alias = false)
    {
        return $this->mergeFields($this->userPublicRawFields(), $prefix, $alias);
    }

}
