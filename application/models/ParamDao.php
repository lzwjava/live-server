<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 2/15/17
 * Time: 6:11 PM
 */
class ParamDao extends BaseDao
{
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
