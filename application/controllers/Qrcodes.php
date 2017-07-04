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
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    private function renderQrcode($text)
    {
        $qrcode = new QrCode();
        $qrcode
            ->setText($text)
            ->setSize(300)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            //->setImageType(QrCode::IMAGE_TYPE_PNG);
            ->setWriterByName('png')
        header('Content-Type: ' . $qrcode->getContentType());
        $qrcode->render();
    }

    function qrcode_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_TEXT))) {
            return;
        }
        $text = $this->get(KEY_TEXT);
        $this->renderQrcode($text);
    }

}
