<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/6/16
 * Time: 11:01 PM
 */

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class QiniuDao extends BaseDao
{
    private function getUpToken()
    {
        $bucket = 'qulive';
        $auth = new Auth(QINIU_ACCESS_KEY, QINIU_SECRET_KEY);
        $upToken = $auth->uploadToken($bucket);
        return $upToken;
    }

    function getUpTokenResult()
    {
        $upToken = $this->getUpToken();
        $bucketUrl = QINIU_FILE_HOST;
        $result = new StdClass;
        $result->key = getToken(6);
        $result->uptoken = $upToken;
        $result->bucketUrl = $bucketUrl;
        return $result;
    }

    private function fetchImage($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        return $data;
    }

    function fetchImageAndUpload($url)
    {
        $data = $this->fetchImage($url);
        return $this->uploadImage($data);
    }

    private function uploadImage($imageData)
    {
        $upManager = new UploadManager();
        $result = $this->getUpTokenResult();
        list($ret, $error) = $upManager->put($result->uptoken, $result->key, $imageData, null);
        if ($error) {
            return array(null, $error);
        }
        $imageUrl = $result->bucketUrl . '/' . $result->key;
        logInfo("uploaded image " . $imageUrl);
        return array($imageUrl, $error);
    }

}
