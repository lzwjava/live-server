<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 2/15/17
 * Time: 4:52 PM
 */
class JobDao extends BaseDao
{
    function queryAllWaitJobs()
    {
        $now = time();
        $sql = "SELECT * FROM jobs WHERE triggerTs<= ? AND status = ?";
        $binds = array($now, JOB_STATUS_WAIT);
        $jobs = $this->db->query($sql, $binds)->result();
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
        logInfo("insert");
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
        $this->db->insert(TABLE_JOBS, $data);
        $jobId = $this->db->insert_id();
        return $jobId;
    }

    private function updateJob($jobId, $data)
    {
        $this->db->where(KEY_JOB_ID, $jobId);
        $this->db->update(TABLE_JOBS, $data);
        return $this->db->affected_rows() > 0;
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

    function cancelNotifyJobs($live)
    {

    }

    function insertNotifyJobs($live)
    {
        $planTsDate = date_create($live->planTs, new DateTimeZone('Asia/Shanghai'));
        $unixTimestamp = $planTsDate->getTimestamp();
        $ts1 = $unixTimestamp - 60 * 60 * 8;
        $ts2 = $unixTimestamp - 60 * 60 * 1;
        $ts3 = $unixTimestamp;

        $jobId1 = $this->insertJob(JOB_NAME_NOTIFY_LIVE_START, $ts1, array(
            KEY_LIVE_ID => $live->liveId,
            KEY_TYPE => 0
        ));

        $jobId2 = $this->insertJob(JOB_NAME_NOTIFY_LIVE_START, $ts2, array(
            KEY_LIVE_ID => $live->liveId,
            KEY_TYPE => 1
        ));

        $jobId3 = $this->insertJob(JOB_NAME_NOTIFY_LIVE_START, $ts3, array(
            KEY_LIVE_ID => $live->liveId,
            KEY_TYPE => 2
        ));

        if (!$jobId1 || !$jobId2 || !$jobId3) {
            return false;
        }
        return true;
    }

}
