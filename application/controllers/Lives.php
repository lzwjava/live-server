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

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_SUBJECT))) {
            return;
        }
        $subject = $this->post(KEY_SUBJECT);
        $id = $this->liveDao->createLive($subject);
        if (!$id) {
            $this->failure(ERROR_SQL_WRONG);
        } else {
            $this->succeed(array(KEY_ID => $id));
        }
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

    function alive_get()
    {

    }
}
