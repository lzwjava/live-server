<?php
namespace App\Controllers;
use App\Models\UserDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




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

    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userDao = new UserDao();
}


    public function all()
    {
        $cnt = $this->userDao->count();
        $this->succeed(array(TABLE_USERS => $cnt));
    }

}
