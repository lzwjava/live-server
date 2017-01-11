<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/28/16
 * Time: 6:34 AM
 */
class Stats extends BaseController
{

    /** @var UserDao */
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    function all_get()
    {
        $cnt = $this->userDao->count();
        $this->succeed(array(TABLE_USERS => $cnt));
    }

}
