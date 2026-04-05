<?php
namespace App\Controllers;
use App\Models\SubscribeDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/24/17
 * Time: 2:44 AM
 */
class Subscribes extends BaseController
{
    public $subscribeDao;

    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->subscribeDao = new SubscribeDao();
}


    public function create()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_TOPIC_ID))) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $topicId = $this->request->getPost(KEY_TOPIC_ID);
        $subscribeId = $this->subscribeDao->subscribeTopic($user->userId, $topicId);
        $this->succeed(array(KEY_SUBSCRIBE_ID => $subscribeId));
    }

    public function del()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_TOPIC_ID))) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $topicId = $this->request->getPost(KEY_TOPIC_ID);
        $ok = $this->subscribeDao->unsubscribeTopic($user->userId, $topicId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }
}
