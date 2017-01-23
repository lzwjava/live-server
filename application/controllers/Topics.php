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

    function init_get()
    {
        $topicNames = array('后端', '设计', '前端', 'iOS', '产品', '人工智能', '职场', '硅谷', '算法', '读书',
            '创业', '技术成长', '泛技术');
        $this->db->trans_begin();
        foreach ($topicNames as $name) {
            $topicId = $this->topicDao->addTopic($name);
            if (!$topicId) {
                $this->db->trans_rollback();
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
        }
        $this->db->trans_commit();
        $this->succeed();
    }

}
