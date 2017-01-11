<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/28/16
 * Time: 4:53 AM
 */
class Charges extends BaseController
{
    /** @var ChargeDao */
    public $chargeDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
    }

    function one_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_ORDER_NO))) {
            return;
        }
        $orderNo = $this->get(KEY_ORDER_NO);
        $charge = $this->chargeDao->getOneByOrderNo($orderNo);
        $this->succeed($charge);
    }
}
