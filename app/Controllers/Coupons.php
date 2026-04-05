<?php
namespace App\Controllers;
use App\Models\CouponDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/8/16
 * Time: 12:12 AM
 */
class Coupons extends BaseController
{
    public $couponDao;

    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->couponDao = new CouponDao();
}


    public function create()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $phone = $this->request->getPost(KEY_PHONE);
        $liveId = $this->request->getPost(KEY_LIVE_ID);
        $id = $this->couponDao->addCoupon(sha1($phone), $liveId);
        if (!$id) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

}
