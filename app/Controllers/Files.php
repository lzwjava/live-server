<?php
namespace App\Controllers;
use App\Models\QiniuDao;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\JSSDK;




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

    

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->qiniuDao = new QiniuDao();
        $this->jsSdk = new JSSDK();
}


    public function uptoken()
    {
        $this->succeed($this->qiniuDao->getUpTokenResult());
    }

    function wechatToQiniu()
    {
        if ($this->checkIfParamsNotExist($this->request->getGet(), array(KEY_MEDIA_ID))) {
            return;
        }
        $mediaId = $this->request->getGet(KEY_MEDIA_ID);
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
