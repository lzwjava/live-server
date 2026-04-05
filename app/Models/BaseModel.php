<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Class BaseModel
 *
 * Base model for all models in the application
 */
class BaseModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->db->query("SET NAMES UTF8MB4");
    }

    /**
     * Merge fields with table name prefix
     */
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
        return implode(',', $fields);
    }

    /**
     * Get one row from table
     */
    protected function getOneFromTable($table, $field, $value, $fields = "*")
    {
        $sql = "SELECT $fields FROM $table WHERE $field=?";
        $binds = [$value];
        $query = $this->db->query($sql, $binds);
        return $query->getRow();
    }

    /**
     * Get list from table
     */
    protected function getListFromTable($table, $field, $value, $fields = "*", $orderBy = null, $skip = 0, $limit = 100)
    {
        $order = '';
        if ($orderBy) {
            $order = ' ORDER BY ' . $orderBy . ' ';
        }
        $sql = "SELECT $fields FROM $table WHERE $field=? $order LIMIT $limit OFFSET $skip";
        $values = [$value];
        $query = $this->db->query($sql, $values);
        return $query->getResult();
    }

    /**
     * Count rows
     */
    protected function countRows($table, $field, $value)
    {
        $sql = "SELECT count(*) AS cnt FROM $table WHERE $field=?";
        $array = [$value];
        $query = $this->db->query($sql, $array);
        $result = $query->getRow();
        return $result->cnt;
    }

    /**
     * Extract fields from object
     */
    protected function extractFields($object, $fields)
    {
        $newObj = new \StdClass();
        foreach ($fields as $field) {
            $newObj->$field = $object->$field;
            unset($object->$field);
        }
        return $newObj;
    }

    /**
     * Prefix fields
     */
    protected function prefixFields($fields, $prefix)
    {
        foreach ($fields as &$field) {
            $field = $prefix . $field;
        }
        return $fields;
    }

    /**
     * Attendance fields
     */
    protected function attendanceFields()
    {
        return [
            KEY_ATTENDANCE_ID ?? 'attendanceId',
            KEY_USER_ID ?? 'userId',
            KEY_LIVE_ID ?? 'liveId',
            KEY_FROM_USER_ID ?? 'fromUserId',
            KEY_FIRST_NOTIFIED ?? 'firstNotified',
            KEY_PRE_NOTIFIED ?? 'preNotified',
            KEY_NOTIFIED ?? 'notified',
            KEY_WECHAT_NOTIFIED ?? 'wechatNotified',
            KEY_VIDEO_NOTIFIED ?? 'videoNotified',
            KEY_ORDER_NO ?? 'orderNo',
            KEY_CREATED ?? 'created',
            KEY_UPDATED ?? 'updated'
        ];
    }

    protected function attendancePublicFields($prefix = null, $alias = false)
    {
        $prefix = $prefix ?? (defined('TABLE_ATTENDANCES') ? TABLE_ATTENDANCES : 'attendances');
        return $this->mergeFields($this->attendanceFields(), $prefix, $alias);
    }

    /**
     * Live fields
     */
    protected function liveFields()
    {
        return [
            KEY_LIVE_ID ?? 'liveId',
            KEY_SUBJECT ?? 'subject',
            KEY_RTMP_KEY ?? 'rtmpKey',
            KEY_ATTENDANCE_COUNT ?? 'attendanceCount',
            KEY_NEED_PAY ?? 'needPay',
            KEY_COVER_URL ?? 'coverUrl',
            KEY_COURSEWARE_KEY ?? 'coursewareKey',
            KEY_LIVE_QRCODE_KEY ?? 'liveQrcodeKey',
            KEY_PREVIEW_URL ?? 'previewUrl',
            KEY_AMOUNT ?? 'amount',
            KEY_MAX_PEOPLE ?? 'maxPeople',
            KEY_CONVERSATION_ID ?? 'conversationId',
            KEY_STATUS ?? 'status',
            KEY_PLAN_TS ?? 'planTs',
            KEY_BEGIN_TS ?? 'beginTs',
            KEY_END_TS ?? 'endTs',
            KEY_OWNER_ID ?? 'ownerId',
            KEY_SPEAKER_INTRO ?? 'speakerIntro',
            KEY_DETAIL ?? 'detail',
            KEY_NOTICE ?? 'notice',
            KEY_SHARE_ICON ?? 'shareIcon',
            KEY_CREATED ?? 'created',
            KEY_UPDATED ?? 'updated'
        ];
    }

    protected function livePublicFields($prefix = null, $alias = false)
    {
        $prefix = $prefix ?? (defined('TABLE_LIVES') ? TABLE_LIVES : 'lives');
        return $this->mergeFields($this->liveFields(), $prefix, $alias);
    }

    /**
     * User fields
     */
    public function userPublicRawFields()
    {
        return [
            KEY_USER_ID ?? 'userId',
            KEY_AVATAR_URL ?? 'avatarUrl',
            KEY_USERNAME ?? 'username'
        ];
    }

    public function userPublicFields($prefix = null, $alias = false)
    {
        $prefix = $prefix ?? (defined('TABLE_USERS') ? TABLE_USERS : 'users');
        return $this->mergeFields($this->userPublicRawFields(), $prefix, $alias);
    }

    /**
     * Redis client
     */
    protected function newRedisClient($database, $prefix)
    {
        return new \Predis\Client([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => 'my_redis',
            'database' => $database
        ], [
            'prefix' => $prefix
        ]);
    }
}

// Namespace bridge: allow App\Libraries\BaseModel → App\Models\BaseModel
class_alias('App\Models\BaseModel', 'App\Libraries\BaseModel');
