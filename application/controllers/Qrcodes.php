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
        $qrCode = new QrCode();
        $qrCode
            ->setText($text)
            ->setSize(300)
            ->setMargin(10)
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setWriterByName('png');
        header('Content-Type: ' . $qrCode->getContentType());
        $this->output->set_status_header(200)
            ->set_content_type($qrCode->getContentType(), 'utf-8')
            ->set_output($qrCode->writeString());
      
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
