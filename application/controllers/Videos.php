<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/9/16
 * Time: 5:41 AM
 */
class Videos extends BaseController
{
    public $liveDao;
    public $videoDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(VideoDao::class);
        $this->videoDao = new VideoDao();
    }

    function one_get()
    {

    }

    function list_get($liveId)
    {
        $live = $this->liveDao->getLive($liveId);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        $list = $this->videoDao->getVideosByLiveId($liveId);
        $this->succeed($list);
    }
}
