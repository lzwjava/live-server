<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/28/16
 * Time: 8:29 PM
 */
class Jobs extends BaseController
{
    public $statusDao;
    public $jobDao;
    public $jobHelperDao;
    public $paramDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(StatusDao::class);
        $this->statusDao = new StatusDao();
        $this->load->model(JobDao::class);
        $this->jobDao = new JobDao();
        $this->load->model(JobHelperDao::class);
        $this->jobHelperDao = new JobHelperDao();
        $this->load->model(ParamDao::class);
        $this->paramDao = new ParamDao();
    }

    function alive_get()
    {
        //$this->statusDao->cleanStatus();
        $taskRunning = $this->paramDao->queryTaskRunning();
        if ($taskRunning == '1') {
            logInfo("task already running");
            return;
        }
        $this->paramDao->setTaskRunning('1');

        $waitTodoJobs = $this->jobDao->queryAllWaitJobs();
        foreach ($waitTodoJobs as $waitJob) {
            $this->jobDao->updateJobStatus($waitJob->jobId, JOB_STATUS_DOING);
            if ($waitJob->name == JOB_NAME_NOTIFY_LIVE_START) {
                $params = $waitJob->params;
                $result = $this->jobHelperDao->notifyLiveStartWithType($params->liveId,
                    $params->type);
                $this->jobDao->updateJobStatusReport($waitJob->jobId,
                    JOB_STATUS_DONE, json_encode($result));
            } else {

            }
        }
        $this->paramDao->setTaskRunning('0');
        $this->succeed();
    }

    function queue_get()
    {
        $op = $this->get('op');
        if ($op == 'get') {
            $this->getMessageQueue();
        } else {
            $this->addMessageQueue();
        }
    }

    private
    function getMessageQueue()
    {
        $queue = msg_get_queue(TRANSCODE_QUEUE);
        for (; ;) {
            $msg = NULL;
            $msgType = NULL;
            if (msg_receive($queue, 1, $msgType, 1024, $msg)) {
                logInfo("receive msg type:" . $msgType . " mgs:" . json_encode($msg));
            }
            usleep(1000 * 100);
        }
    }

    private
    function addMessageQueue()
    {
        $queue = msg_get_queue(TRANSCODE_QUEUE);
        $object = new stdclass;
        $object->name = 'foo';
        $object->id = uniqid();
        if (msg_send($queue, 1, $object)) {
            logInfo("added to queue, stat: " . json_encode(msg_stat_queue($queue)));
        } else {
            logInfo("could not add message to queue \n");
        }
    }

}
