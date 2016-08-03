<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/28/16
 * Time: 12:46 AM
 */
class Lives extends BaseController
{
    public $liveDao;
    public $statusDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(StatusDao::class);
        $this->statusDao = new StatusDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_SUBJECT, KEY_COVER_URL))) {
            return;
        }
        $subject = $this->post(KEY_SUBJECT);
        $coverUrl = $this->post(KEY_COVER_URL);
        $id = $this->liveDao->createLive($subject, $coverUrl);
        if (!$id) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $ok = $this->statusDao->open($id);
        if (!$ok) {
            $this->failure(ERROR_REDIS_WRONG);
            return;
        }
        $live = $this->liveDao->getLive($id);
        $this->succeed($live);
    }

    function list_get()
    {
        $lives = $this->liveDao->getLivingLives();
        $this->succeed($lives);
    }

    function one_get($id)
    {
        $live = $this->liveDao->getLive($id);
        $this->succeed($live);
    }

    function alive_get($id)
    {
        $ok = $this->statusDao->alive($id);
        if (!$ok) {
            $this->failure(ERROR_ALIVE_FAIL);
            return;
        }
        $this->succeed($ok);
    }

    function end_get($id)
    {
        $live = $this->liveDao->getLive($id);
        if (!$live) {
            $this->failure(ERROR_OBJECT_NOT_EXIST);
            return;
        }
        $this->statusDao->endLive($id);
        $this->succeed();
    }

}
