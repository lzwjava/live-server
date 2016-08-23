<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 8/17/16
 * Time: 3:19 PM
 */

use Endroid\QrCode\QrCode;

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

    private function checkCodeInvalid($code)
    {
        if (!preg_match('/quzhibo-[a-zA-Z0-9]{32}/', $code)) {
            $this->failure(ERROR_QRCODE_INVALID);
            return true;
        }
        return false;
    }

    function scanQrcode_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_CODE))) {
            return;
        }
        $code = $this->post(KEY_CODE);
        if ($this->checkCodeInvalid($code)) {
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
        $secs = 0;
        while ($secs < 8) {
            $qrcode = $this->qrcodeDao->getQrcode($code);
            if (!$qrcode) {
                sleep(1);
                $secs++;
            } else {
                break;
            }
        }
        $qrcode = $this->qrcodeDao->getQrcode($code);
        if (!$qrcode) {
            $this->succeed(array(KEY_SCANNED => false));
        } else {
            $this->userDao->setLoginByUserId($qrcode->userId);
            $this->succeed(array(KEY_SCANNED => true));
        }
    }

    function png_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE))) {
            return;
        }
        $code = $this->get(KEY_CODE);
        if ($this->checkCodeInvalid($code)) {
            return;
        }
        $qrcode = new QrCode();
        $qrcode
            ->setText($code)
            ->setSize(300)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        header('Content-Type: ' . $qrcode->getContentType());
        $qrcode->render();
    }

}
