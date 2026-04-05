<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/24/17
 * Time: 2:44 AM
 */
class Subscribes extends BaseController
{
    public $subscribeDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(SubscribeDao::class);
        $this->subscribeDao = new SubscribeDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_TOPIC_ID))) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $topicId = $this->post(KEY_TOPIC_ID);
        $subscribeId = $this->subscribeDao->subscribeTopic($user->userId, $topicId);
        $this->succeed(array(KEY_SUBSCRIBE_ID => $subscribeId));
    }

    function del_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_TOPIC_ID))) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $topicId = $this->post(KEY_TOPIC_ID);
        $ok = $this->subscribeDao->unsubscribeTopic($user->userId, $topicId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }
}
