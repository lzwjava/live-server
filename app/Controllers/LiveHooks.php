<?php
namespace App\Controllers;
use App\Models\VideoDao;
use App\Models\RecordedVideoDao;
use App\Models\LiveDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/16/16
 * Time: 1:32 AM
 */
class LiveHooks extends BaseController
{
    /** @var LiveDao */
    public $liveDao;
    public $recordedVideoDao;

    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->liveDao = new LiveDao();
        $this->recordedVideoDao = new RecordedVideoDao();
}


    function onPublish()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_ACTION, KEY_STREAM))) {
            return;
        }
        $action = $this->request->getPost(KEY_ACTION);
        $stream = $this->request->getPost(KEY_STREAM);
        if ($action != 'on_publish') {
            $this->failure(ERROR_PARAMETER_ILLEGAL);
            return;
        }
        if (strpos(strrev($stream), 'ff') === 0) {
            // transcode stream
        } else {
        }
        echo 0;
    }

    function onUnPublish()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_ACTION, KEY_STREAM))) {
            return;
        }
        $action = $this->request->getPost(KEY_ACTION);
        $stream = $this->request->getPost(KEY_STREAM);
        if ($action != 'on_unpublish') {
            $this->failure(ERROR_PARAMETER_ILLEGAL);
            return;
        }
        if (strpos(strrev($stream), 'ff') === 0) {
            // transcode stream
        } else {

        }
        echo 0;
    }

    private function fileNameByPath($path)
    {
        $output = array();
        $res = preg_match('/.*\/(.*\.flv)/', $path, $output);
        if (!$res) {
            return null;
        }
        return $output[1];
    }

    function onDvr()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_ACTION, KEY_STREAM))) {
            return;
        }
        $action = $this->request->getPost(KEY_ACTION);
        $stream = $this->request->getPost(KEY_STREAM);
        $file = $this->request->getPost(KEY_FILE);
        if ($action != 'on_dvr') {
            $this->failure(ERROR_PARAMETER_ILLEGAL);
            return;
        }
        $live = $this->liveDao->getLiveByRtmpKey($stream);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $fileName = $this->fileNameByPath($file);
        $ok = $this->recordedVideoDao->addVideo($live->liveId, $fileName);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        echo 0;
    }

}
