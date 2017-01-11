<?php

class JSSDK
{
    private $appId;
    private $appSecret;
    /** @var WxDao */
    public $wxDao;

    public function __construct($appId = null, $appSecret = null)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $ci = get_instance();
        $ci->load->model(WxDao::class);
        $this->wxDao = new WxDao();
    }

    public function getSignPackage($url)
    {
        $jsapiTicket = $this->getJsApiTicket();
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket()
    {
        if (isDebug()) {
            return TMP_WECHAT_JSAPI_TICKET;
        }
        $ticket = $this->wxDao->getJSApiTicket();
        if (!$ticket) {
            $accessToken = $this->getAccessToken();
            $url = WECHAT_API_CGIBIN . 'ticket/getticket';
            $query = array(
                'type' => 'jsapi',
                'access_token' => $accessToken
            );
            list($error, $data) = $this->httpGet($url, $query);
            if (!$error) {
                $ticket = $data->ticket;
            }
            if ($ticket) {
                $this->wxDao->setJSApiTicket($ticket, $data->expires_in);
            }
            return $ticket;
        } else {
            return $ticket;
        }
    }

    function getAccessToken()
    {
        if (isDebug()) {
            return TMP_WECHAT_ACCESS_TOKEN;
        }
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $accessToken = $this->wxDao->getAccessToken();
        if (!$accessToken) {
            $url = WECHAT_API_CGIBIN . 'token';
            $query = array(
                'grant_type' => 'client_credential',
                'appid' => $this->appId,
                'secret' => $this->appSecret
            );
            list($error, $data) = $this->httpGet($url, $query);
            if (!$error) {
                $accessToken = $data->access_token;
            }
            if ($accessToken) {
                $this->wxDao->setAccessToken($accessToken, $data->expires_in);
            }
            return $accessToken;
        } else {
            return $accessToken;
        }
    }

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

    function wechatHttpPost($path, $data)
    {
        $accessToken = $this->getAccessToken();
        $url = WECHAT_API_CGIBIN . $path . '?access_token='
            . $accessToken;
        return $this->httpPost($url, $data);
    }

    function fetchWxappSessionKey($code)
    {
        if (isDebug()) {
            $result = array(null, array('openid' => 'abc', 'session_key' => 'wrewfoiodv'));
            return $result;
        }
        $url = WECHAT_API_BASE . 'sns/jscode2session';
        $data = array(
            'appid' => WXAPP_APPID,
            'secret' => WXAPP_SECRET,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        );
        return $this->httpGet($url, $data);
    }

    function wechatHttpGet($path, $params = array())
    {
        $accessToken = $this->getAccessToken();
        $url = WECHAT_API_CGIBIN . $path;
        $params['access_token'] = $accessToken;
        return $this->httpGet($url, $params);
    }

    private function parseResponse($respStr)
    {
        $error = null;
        $data = null;
        if ($respStr === false) {
            $error = 'network error';
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

    function httpPost($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($data == null) {
            $data = new stdClass();
        }
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded);
        $res = curl_exec($curl);
        curl_close($curl);
        return $this->parseResponse($res);
    }


    function httpGetUserInfo($accessToken, $openId)
    {
        $url = WECHAT_API_BASE . 'sns/userinfo';
        $query = array(
            'access_token' => $accessToken,
            'openid' => $openId,
            'lang' => 'zh_CN'
        );
        return $this->httpGet($url, $query);
    }

    function queryIsSubscribeByOpenId($openId)
    {
        $accessToken = $this->getAccessToken();
        list($error, $weUser) = $this->httpGetUserInfoByPlatform($accessToken, $openId);
        if ($error) {
            logInfo("fetch user info error " . $error);
            return array(ERROR_USER_INFO_FAILED, 0);
        }
        return array(null, $weUser->subscribe);
    }

    function httpGetUserInfoByPlatform($accessToken, $openId)
    {
        $url = WECHAT_API_CGIBIN . 'user/info';
        $query = array(
            'access_token' => $accessToken,
            'openid' => $openId,
            'lang' => 'zh_CN'
        );
        return $this->httpGet($url, $query);
    }

    private function baseHttpGetAccessToken($code, $wechatAppId, $wechatSecret)
    {
        $url = WECHAT_API_BASE . 'sns/oauth2/access_token';
        $query = array(
            'appid' => $wechatAppId,
            'secret' => $wechatSecret,
            'grant_type' => 'authorization_code',
            'code' => $code
        );
        return $this->httpGet($url, $query);
    }

    function httpGetUnionId($accessToken, $openId)
    {
        $url = WECHAT_API_BASE . 'sns/userinfo';
        $data = array(
            'access_token' => $accessToken,
            'openid' => $openId
        );
        return $this->httpGet($url, $data);
    }

    function getUnionId($accessToken, $openId)
    {
        list($error, $unionResult) = $this->httpGetUnionId($accessToken, $openId);
        if ($error) {
            logInfo("failed union result: " . json_encode($unionResult));
            return array(null, $error->data);
        } else {
            return array($unionResult->unionid, 0);
        }
    }

    function httpGetAccessToken($code)
    {
        return $this->baseHttpGetAccessToken($code, WECHAT_APP_ID, WECHAT_APP_SECRET);
    }

    function webHttpGetAccessToken($code)
    {
        return $this->baseHttpGetAccessToken($code, WEB_WECHAT_APP_ID, WEB_WECHAT_APP_SECRET);
    }

    function appHttpGetAccessToken($code)
    {
        return $this->baseHttpGetAccessToken($code, MOBILE_WECHAT_APP_ID, MOBILE_WECHAT_APP_SECRET);
    }

    function genQrcode($sceneData)
    {
        $accessToken = $this->getAccessToken();
        $url = WECHAT_API_CGIBIN . 'qrcode/create?access_token='
            . $accessToken;
        $data = array(
            'expire_seconds' => 60 * 60,
            'action_name' => 'QR_LIMIT_STR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_str' => json_encode($sceneData)
                )
            )
        );
        return $this->httpPost($url, $data);
    }

}

