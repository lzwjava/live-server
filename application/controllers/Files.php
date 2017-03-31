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
    public $jsSdk;

    function __construct()
    {
        parent::__construct();
        $this->load->model(QiniuDao::class);
        $this->qiniuDao = new QiniuDao();
        $this->load->library(JSSDK::class);
        $this->jsSdk = new JSSDK();
    }

    function uptoken_get()
    {
        $this->succeed($this->qiniuDao->getUpTokenResult());
    }

    function wechatToQiniu_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_MEDIA_ID))) {
            return;
        }
        $mediaId = $this->get(KEY_MEDIA_ID);
        logInfo("mediaId: $mediaId");
        $baseUrl = 'http://file.api.weixin.qq.com/cgi-bin/media/get';
        $query = array(
            'access_token' => $this->jsSdk->getAccessToken(),
            'media_id' => $mediaId
        );
        $url = $baseUrl . '?' . http_build_query($query);
        list($imageUrl, $imageKey, $error) = $this->qiniuDao->fetchImageAndUpload($url);
        if ($error) {
            $this->failure(ERROR_QINIU_UPLOAD);
            return;
        }
        $this->succeed(array(
            KEY_URL => $imageUrl,
            KEY_KEY => $imageKey
        ));
    }
}
