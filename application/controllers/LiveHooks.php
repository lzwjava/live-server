<?php

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

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(RecordedVideoDao::class);
        $this->recordedVideoDao = new RecordedVideoDao();
    }

    function onPublish_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_ACTION, KEY_STREAM))) {
            return;
        }
        $action = $this->post(KEY_ACTION);
        $stream = $this->post(KEY_STREAM);
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

    function onUnPublish_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_ACTION, KEY_STREAM))) {
            return;
        }
        $action = $this->post(KEY_ACTION);
        $stream = $this->post(KEY_STREAM);
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

    function onDvr_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_ACTION, KEY_STREAM))) {
            return;
        }
        $action = $this->post(KEY_ACTION);
        $stream = $this->post(KEY_STREAM);
        $file = $this->post(KEY_FILE);
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
