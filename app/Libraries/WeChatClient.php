<?php

namespace App\Libraries;

/**
 * WeChatClient - Simple HTTP client for WeChat API
 * Plain class, no CI3 dependencies
 */
class WeChatClient
{
    public function httpGet($baseUrl, $query = [])
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
        if ($respStr === false) {
            return ['curl error', null];
        }
        $respData = json_decode($respStr);
        if (isset($respData->errcode) && $respData->errcode != 0) {
            return [$respData->errmsg, $respData->errcode];
        }
        return [null, $respData];
    }

    public function httpPost($baseUrl, $data = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $baseUrl);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $res = curl_exec($curl);
        curl_close($curl);
        return $this->parseResponse($res);
    }
}