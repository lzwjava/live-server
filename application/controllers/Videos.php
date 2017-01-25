<?php

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

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(VideoDao::class);
        $this->videoDao = new VideoDao();
    }

    function import_get()
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

    function list_get($liveId)
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

    function create_post($liveId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_FILE_NAME, KEY_TITLE))
        ) {
            return;
        }
        $fileName = $this->post(KEY_FILE_NAME);
        $title = $this->post(KEY_TITLE);
        $id = $this->videoDao->addVideo($liveId, $title, $fileName, VIDEO_TYPE_MP4);
        if (!$id) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

}
