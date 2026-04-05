<?php
namespace App\Controllers;
use App\Models\UserDao;
use Endroid\QrCode\QrCode;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;




/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 8/17/16
 * Time: 3:19 PM
 */


class Qrcodes extends BaseController
{
    public $userDao;

    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
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

    public function qrcode()
    {
        if ($this->checkIfParamsNotExist($this->request->getGet(), array(KEY_TEXT))) {
            return;
        }
        $text = $this->request->getGet(KEY_TEXT);
        $this->renderQrcode($text);
    }

}
