<?php
namespace App\Controllers;
use App\Models\AccountDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




/**
 * Accounts Controller
 */
class Accounts extends BaseController
{
    protected $accountDao;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
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
