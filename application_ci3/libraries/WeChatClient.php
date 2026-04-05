<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/21/17
 * Time: 9:00 PM
 */
class WeChatClient
{
    function httpGet($baseUrl, $query = array())
    {
        $url = $baseUrl . '?' . http_build_query($query);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $this->parseResponse($res);
    }

    private function parseResponse($respStr)
    {
        $error = null;
        $data = null;
        if ($respStr === false) {
            $error = 'curl error';
        } else {
            $respData = json_decode($respStr);
            if (isset($respData->errcode) && $respData->errcode != 0) {
                $error = $respData->errmsg;
                $data = $respData->errcode;
            } else {
                $error = null;
                $data = $respData;
            }
        }
        return array($error, $data);
    }

    function httpPost($baseUrl, $query = array(), $data, $isFile = false)
    {
        $url = $baseUrl . '?' . http_build_query($query);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($data == null) {
            $data = new stdClass();
        }
        $postFields = null;
        if ($isFile) {
            $postFields = $data;
        } else {
            $postFields = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        $res = curl_exec($curl);
        curl_close($curl);
        return $this->parseResponse($res);
    }

}