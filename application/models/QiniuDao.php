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
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        if (curl_errno($ch) == CURLE_OPERATION_TIMEOUTED) {
            curl_close($ch);
            logInfo("curl timeout");
            return array(null, ERROR_QINIU_UPLOAD);
        }
        if ($data == null) {
            logInfo('curl error: ' . curl_error($ch));
            curl_close($ch);
            return array(null, ERROR_QINIU_UPLOAD);
        }
        curl_close($ch);
        if ($data) {
            $jsonRep = json_decode($data);
            if ($jsonRep && $jsonRep->errcode != 0) {
                logInfo("qiniu unload error $data");
                return array(null, ERROR_QINIU_UPLOAD);
            }
        }
        return array($data, null);
    }

    function fetchImageAndUpload($url)
    {
        list($data, $error) = $this->fetchImage($url);
        if ($error) {
            return array(null, null, $error);
        }
        return $this->uploadImage($data);
    }

    private function uploadImage($imageData)
    {
        $upManager = new UploadManager();
        $result = $this->getUpTokenResult();
        list($ret, $error) = $upManager->put($result->uptoken, $result->key, $imageData, null);
        if ($error) {
            logInfo("upload error");
            return array(null, $error);
        }
        $imageUrl = $result->bucketUrl . '/' . $result->key;
        return array($imageUrl, $result->key, $error);
    }

}
