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

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
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
            $live = $this->liveDao->getLiveByRtmpKey($stream);
            if ($this->checkIfObjectNotExists($live)) {
                return;
            }
            $ok = $this->liveDao->beginLive($live->liveId);
            if (!$ok) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
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
            $live = $this->liveDao->getLiveByRtmpKey($stream);
            if ($this->checkIfObjectNotExists($live)) {
                return;
            }
            $ok = $this->liveDao->leaveLive($live->liveId);
            if (!$ok) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
        }
        echo 0;
    }

}
