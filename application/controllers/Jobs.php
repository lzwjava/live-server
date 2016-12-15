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

    function __construct()
    {
        parent::__construct();
        $this->load->model(StatusDao::class);
        $this->statusDao = new StatusDao();
    }

    function alive_get()
    {
        //$this->statusDao->cleanStatus();
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
