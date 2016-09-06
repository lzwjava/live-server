<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/2/6
 * Time: 上午2:12
 */
class Files extends BaseController
{
    public $qiniuDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(QiniuDao::class);
        $this->qiniuDao = new QiniuDao();
    }

    public function uptoken_get()
    {
        $this->succeed($this->qiniuDao->getUpTokenResult());
    }
}
