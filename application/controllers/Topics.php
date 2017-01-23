<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/24/17
 * Time: 1:37 AM
 */
class Topics extends BaseController
{
    /** @var TopicDao */
    public $topicDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(TopicDao::class);
        $this->topicDao = new TopicDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_NAME))) {
            return;
        }
        $name = $this->post(KEY_NAME);
        $topicId = $this->topicDao->addTopic($name);
        $this->succeed(array(KEY_TOPIC_ID => $topicId));
    }

    function list_get()
    {
        $topics = $this->topicDao->getTopics();
        $this->succeed($topics);
    }
}