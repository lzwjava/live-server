<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 2/15/17
 * Time: 4:52 PM
 */
class JobDao extends BaseDao
{
    protected $table = 'jobs';

    function queryAllWaitJobs()
    {
        $now = time();
        $sql = "SELECT * FROM jobs WHERE triggerTs<= ? AND status = ?";
        $binds = array($now, JOB_STATUS_WAIT);
        $jobs = $this->db->query($sql, $binds)->getResult();
        foreach ($jobs as $job) {
            if ($job->params) {
                $job->params = json_decode($job->params);
            }
        }
        return $jobs;
    }

    function insertJob($name, $triggerTs, $params = null)
    {
        $currentTime = time();
        if ($triggerTs <= $currentTime) {
            logInfo("invalid job");
            return true;
        }
        $paramStr = '{}';
        if ($params) {
            $paramStr = json_encode($params);
        }
        $data = array(
            KEY_NAME => $name,
            KEY_PARAMS => $paramStr,
            KEY_TRIGGER_TS => $triggerTs,
            KEY_STATUS => JOB_STATUS_WAIT
        );
        $this->db->table(TABLE_JOBS)->insert($data);
        $jobId = $this->db->insertID();
        return $jobId;
    }

    private function updateJob($jobId, $data)
    {
        return $this->db->table(TABLE_JOBS)->where(KEY_JOB_ID, $jobId)->update($data) !== false;
    }

    function updateJobStatus($jobId, $status)
    {
        return $this->updateJob($jobId, array(
            KEY_STATUS => $status
        ));
    }

    function updateJobStatusReport($jobId, $status, $report = '')
    {
        return $this->updateJob($jobId, array(
            KEY_STATUS => $status,
            KEY_REPORT => $report
        ));
    }

    private function cancelOneNotifyJob($params)
    {
        $data = array(
            KEY_STATUS => JOB_STATUS_CANCEL
        );
        return $this->db->table(TABLE_JOBS)->where(KEY_PARAMS, json_encode($params))->where(KEY_NAME, JOB_NAME_NOTIFY_LIVE_START)->update($data) !== false;
    }

    function cancelNotifyJobs($live)
    {
        $this->cancelOneNotifyJob(array(
            KEY_LIVE_ID => $live->liveId,
            KEY_TYPE => 0
        ));
        $this->cancelOneNotifyJob(array(
            KEY_LIVE_ID => $live->liveId,
            KEY_TYPE => 1
        ));
        $this->cancelOneNotifyJob(array(
            KEY_LIVE_ID => $live->liveId,
            KEY_TYPE => 2
        ));
    }

    function insertNotifyJobs($live)
    {
        $this->cancelNotifyJobs($live);

        $planTsDate = date_create($live->planTs, new DateTimeZone('Asia/Shanghai'));
        $unixTimestamp = $planTsDate->getTimestamp();
//        $ts1 = $unixTimestamp - 60 * 60 * 8;
        $ts2 = $unixTimestamp - 60 * 60 * 3;
        $ts3 = $unixTimestamp;

//        $jobId1 = $this->insertJob(JOB_NAME_NOTIFY_LIVE_START, $ts1, array(
//            KEY_LIVE_ID => $live->liveId,
//            KEY_TYPE => 0
//        ));

        $jobId2 = $this->insertJob(JOB_NAME_NOTIFY_LIVE_START, $ts2, array(
            KEY_LIVE_ID => $live->liveId,
            KEY_TYPE => 1
        ));

        $jobId3 = $this->insertJob(JOB_NAME_NOTIFY_LIVE_START, $ts3, array(
            KEY_LIVE_ID => $live->liveId,
            KEY_TYPE => 2
        ));

        if (!$jobId2 || !$jobId3) {
            return false;
        }
        return true;
    }

}

// Namespace bridge: allow App\Libraries\JobDao → App\Models\JobDao
class_alias('App\Models\JobDao', 'App\Libraries\JobDao');
