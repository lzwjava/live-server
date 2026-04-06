<?php

namespace App\Models;

use CodeIgniter\Model;
use Predis\Client as PredisClient;

/**
 * BaseDao - CodeIgniter 4 compatible base model
 * Migrated from CI3 BaseDao
 */
class BaseDao extends Model
{
    // Override in subclasses to set the actual table name
    protected $table = '';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowCallbacks = true;

    public function __construct()
    {
        parent::__construct();
        $this->db->query("SET NAMES utf8mb4");
    }

    protected function mergeFields(array $fields, ?string $tableName = null, bool $alias = false): string
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

    protected function getOneFromTable(string $table, string $field, $value, string $fields = "*")
    {
        return $this->db->query("SELECT $fields FROM $table WHERE $field = ?", [$value])->getRow();
    }

    protected function getListFromTable(string $table, string $field, $value, string $fields = "*", ?string $orderBy = null, int $skip = 0, int $limit = 100)
    {
        $order = $orderBy ? "ORDER BY $orderBy" : "";
        return $this->db->query("SELECT $fields FROM $table WHERE $field = ? $order LIMIT $limit OFFSET $skip", [$value])->getResult();
    }

    protected function countRows(string $table, string $field, $value): int
    {
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM $table WHERE $field = ?", [$value])->getRow();
        return (int) $result->cnt;
    }

    protected function extractFields($object, array $fields)
    {
        $newObj = new \stdClass();
        foreach ($fields as $field) {
            $newObj->$field = $object->$field ?? null;
            unset($object->$field);
        }
        return $newObj;
    }

    protected function prefixFields(array $fields, string $prefix): array
    {
        foreach ($fields as &$field) {
            $field = $prefix . $field;
        }
        return $fields;
    }

    protected function attendanceFields(): array
    {
        return [
            'attendance_id', 'user_id', 'live_id', 'from_user_id', 'first_notified',
            'pre_notified', 'notified', 'wechat_notified', 'video_notified', 'order_no',
            'created', 'updated'
        ];
    }

    protected function attendancePublicFields(string $prefix = 'attendances.', bool $alias = false): string
    {
        return $this->mergeFields($this->attendanceFields(), $prefix, $alias);
    }

    protected function liveFields(): array
    {
        return [
            'live_id', 'subject', 'rtmp_key', 'attendance_count', 'need_pay',
            'cover_url', 'courseware_key', 'live_qrcode_key', 'preview_url', 'amount',
            'max_people', 'conversation_id', 'status', 'plan_ts', 'begin_ts', 'end_ts',
            'owner_id', 'speaker_intro', 'detail', 'notice', 'share_icon', 'created', 'updated'
        ];
    }

    protected function livePublicFields(string $prefix = 'lives.', bool $alias = false): string
    {
        return $this->mergeFields($this->liveFields(), $prefix, $alias);
    }

    protected function userPublicRawFields(): array
    {
        return ['user_id', 'avatar_url', 'username'];
    }

    protected function userPublicFields(string $prefix = 'users.', bool $alias = false): string
    {
        return $this->mergeFields($this->userPublicRawFields(), $prefix, $alias);
    }

    protected function newRedisClient(int $database = 0, string $prefix = ''): PredisClient
    {
        return new PredisClient([
            'scheme' => 'tcp',
            'host' => getenv('REDIS_HOST') ?: getenv('redis.host') ?: '127.0.0.1',
            'port' => 6379,
            'password' => getenv('redis.password') ?: null,
            'database' => $database,
        ], [
            'prefix' => $prefix,
        ]);
    }
}

// Namespace bridge: allow App\Libraries\BaseDao → App\Models\BaseDao
class_alias('App\Models\BaseDao', 'App\Libraries\BaseDao');
