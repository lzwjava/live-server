<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 2/15/17
 * Time: 6:11 PM
 */
class ParamDao extends BaseDao
{
    protected $table = 'params';

    private $client;

    function __construct()
    {
        parent::__construct();
        $this->client = $this->newRedisClient(0, 'global:');
    }

    function setTaskRunning($value)
    {
        $this->client->set(KEY_TASK_RUNNING, $value);
    }

    function getTaskRunning()
    {
        $taskRunning = $this->client->get(KEY_TASK_RUNNING);
        if ($taskRunning) {
            return $taskRunning;
        } else {
            return 0;
        }
    }

}

// Namespace bridge: allow App\Libraries\ParamDao → App\Models\ParamDao
class_alias('App\Models\ParamDao', 'App\Libraries\ParamDao');
