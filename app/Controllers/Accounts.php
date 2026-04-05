<?php

namespace App\Controllers;

use App\Models\AccountDao;

/**
 * Accounts Controller
 */
class Accounts extends BaseController
{
    protected $accountDao;

    public function __construct()
    {
        $this->accountDao = new AccountDao();
    }

    public function me()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $account = $this->accountDao->getOrCreateAccount($user->userId);
        return $this->succeed($account);
    }

    public function initIncome()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $ok = $this->accountDao->initIncome();
        if (!$ok) {
            return $this->failure(ERROR_SQL_WRONG);
        }
        return $this->succeed();
    }
}
