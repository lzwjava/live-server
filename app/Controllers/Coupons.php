<?php

use AppModelsCouponDao;

namespace App\Controllers;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/8/16
 * Time: 12:12 AM
 */
class Coupons extends BaseController
{
    public $couponDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(CouponDao::class);
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
