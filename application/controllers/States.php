<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/10/16
 * Time: 4:14 AM
 */
class States extends BaseController
{
    public $stateDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(StateDao::class);
        $this->stateDao = new StateDao();
        $this->load->helper('string');
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_LIVE_ID))) {
            return;
        }
        $liveId = $this->post(KEY_LIVE_ID);
        $hash = random_string('alnum', 12);
        $id = $this->stateDao->addState($hash, $liveId);
        if (!$id) {
            $this->failure(ERROR_SQL_WRONG);
        }
        $state = $this->stateDao->getState($hash);
        $this->succeed($state);
    }
}
