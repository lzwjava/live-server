<?php
namespace App\Controllers;
use App\Models\StatusDao;
use App\Models\ParamDao;
use App\Models\JobHelperDao;
use App\Models\JobDao;
use App\Models\HelperDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




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

    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->statusDao = new StatusDao();
        $this->jobDao = new JobDao();
        $this->jobHelperDao = new JobHelperDao();
        $this->paramDao = new ParamDao();
}


    public function alive()
    {
        //$this->statusDao->cleanStatus();
        $taskRunning = $this->paramDao->getTaskRunning();
        if ($taskRunning) {
            return;
        }
        $this->paramDao->setTaskRunning(1);

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

        $this->paramDao->setTaskRunning(0);
        $this->succeed();
    }

    public function queue()
    {
        $op = $this->request->getGet('op');
        if ($op == 'get') {
            $this->getMessageQueue();
        } else {
            $this->addMessageQueue();
        }
    }

    private function getMessageQueue()
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

    private function addMessageQueue()
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
