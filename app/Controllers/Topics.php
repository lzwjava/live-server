<?php
namespace App\Controllers;
use App\Models\TopicDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




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


    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->topicDao = new TopicDao();
}


    public function create()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_NAME))) {
            return;
        }
        $name = $this->request->getPost(KEY_NAME);
        $topicId = $this->topicDao->addTopic($name);
        $this->succeed(array(KEY_TOPIC_ID => $topicId));
    }

    public function list()
    {
        $topics = $this->topicDao->getTopics();
        $this->succeed($topics);
    }

    public function init()
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
