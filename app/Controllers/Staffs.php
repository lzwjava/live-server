<?php
namespace App\Controllers;
use App\Models\StaffDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/28/16
 * Time: 4:27 AM
 */
class Staffs extends BaseController
{
    /** @var StaffDao */
    public $staffDao;

    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->staffDao = new StaffDao();
}


    public function create()
    {
        $key = $this->request->getPost('key');
        if ($key != 'BornToBeProud') {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($this->staffDao->isStaff($user->userId)) {
            $this->failure(ERROR_ALREADY_STAFF);
        }
        $ok = $this->staffDao->addStaff($user->userId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

    public function list()
    {
        $staffs = $this->staffDao->getStaffIds();
        $this->succeed($staffs);
    }
}
