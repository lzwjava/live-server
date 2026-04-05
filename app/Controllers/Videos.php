<?php
namespace App\Controllers;
use App\Models\VideoDao;
use App\Models\LiveDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/14/16
 * Time: 3:03 AM
 */
class Videos extends BaseController
{
    /**@var VideoDao */
    public $videoDao;
    /**@var LiveDao */
    public $liveDao;

    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->liveDao = new LiveDao();
        $this->videoDao = new VideoDao();
}


    public function import()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $lives = $this->liveDao->getRawLivesByStatus(LIVE_STATUS_OFF);
        foreach ($lives as $live) {
            $id = $this->videoDao->addVideo($live->liveId, $live->subject, $live->rtmpKey);
            if (!$id) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
        }
        $this->succeed();
    }

    public function list($liveId)
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId, $user);
        if (!$live->canJoin) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        $list = $this->videoDao->getVideosByLiveId($liveId);
        $this->succeed($list);
    }

    public function create($liveId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_FILE_NAME, KEY_TITLE))
        ) {
            return;
        }
        $fileName = $this->request->getPost(KEY_FILE_NAME);
        $title = $this->request->getPost(KEY_TITLE);
        $videoId = $this->videoDao->addVideo($liveId, $title, $fileName, VIDEO_TYPE_MP4);
        if (!$videoId) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed(array(KEY_VIDEO_ID => $videoId));
    }

    function mp4Ready()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $liveId = $this->request->getGet(KEY_LIVE_ID);
        $ok = $this->videoDao->mp4Ready($liveId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

}
