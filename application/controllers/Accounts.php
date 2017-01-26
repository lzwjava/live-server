<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/26/17
 * Time: 9:30 PM
 */
class Accounts extends BaseController
{
    public $accountDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(AccountDao::class);
        $this->accountDao = new AccountDao();
    }

    function me_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $account = $this->accountDao->getOrCreateAccount($user->userId);
        $this->succeed($account);
    }
}
