<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 8/17/16
 * Time: 3:19 PM
 */
class Qrcodes extends BaseController
{
    public $qrcodeDao;
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(QrcodeDao::class);
        $this->qrcodeDao = new QrcodeDao();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    function scanQrcode_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_CODE))) {
            return;
        }
        $code = $this->post(KEY_CODE);
        if (!preg_match('/quzhibo-[a-zA-Z0-9]{32}/', $code)) {
            $this->failure(ERROR_QRCODE_INVALID);
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $id = $this->qrcodeDao->addQrcode($code, $user->userId);
        if (!$id) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

    function isQrcodeScanned_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE))) {
            return;
        }
        $code = $this->get(KEY_CODE);
        $qrcode = $this->qrcodeDao->getQrcode($code);
        if (!$qrcode) {
            $this->succeed(array(KEY_SCANNED => false));
        } else {
            $this->userDao->setLoginByUserId($qrcode->userId);
            $this->succeed(array(KEY_SCANNED => true));
        }
    }

}
