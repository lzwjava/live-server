<?php

namespace App\Controllers;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;


class Home extends BaseController
{
    public function index()
    {
        return $this->respond([
            'message' => 'Welcome to CodeIgniter 4!',
            'version' => '4.x',
            'app' => 'live-server',
            'status' => 'migrated from CI3'
        ]);
    }

    protected function respond($data)
    {
        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/json')
            ->setJSON($data);
    }
}
