<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/28/16
 * Time: 8:29 PM
 */
class Jobs extends BaseController
{
    public $statusDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(StatusDao::class);
        $this->statusDao = new StatusDao();
    }

    function alive_get()
    {
        $this->statusDao->cleanStatus();
    }
}
