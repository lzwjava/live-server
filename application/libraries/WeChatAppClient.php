<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/21/17
 * Time: 9:11 PM
 */
class WeChatAppClient
{
    /** @var WxAppDao */
    public $wxAppDao;

    /** @var WeChatClient */
    public $weChatClient;

    private $appId;
    private $appSecret;

    function __construct()
    {
        $this->appId = WXAPP_APPID;
        $this->appSecret = WXAPP_SECRET;
    }

    function getAccessToken()
    {
        if (isDebug()) {
            return TMP_WXAPP_ACCESS_TOKEN;
        }
        $accessToken = $this->wxAppDao->getAccessToken();
        if (!$accessToken) {
            $url = WECHAT_API_CGIBIN . 'token';
            $query = array(
                'grant_type' => 'client_credential',
                'appid' => $this->appId,
                'secret' => $this->appSecret
            );
            list($error, $data) = $this->weChatClient->httpGet($url, $query);
            if ($error) {
                return array($error, null);
            } else {
                $accessToken = $data->access_token;
                $this->wxAppDao->setAccessToken($accessToken, $data->expires_in - 60);
                return array(null, $accessToken);
            }
        } else {
            return array(null, $accessToken);
        }
    }

    function sendTmplMsg()
    {
        $url = WECHAT_API_CGIBIN . 'message/wxopen/template/send';
    }
}
