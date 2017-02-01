<?php

class JSSDK
{
    private $appId;
    private $appSecret;
    /** @var WxDao */
    public $wxDao;

    /** @var  WeChatClient */
    public $weChatClient;

    public function __construct($appId = null, $appSecret = null)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $ci = get_instance();
        $ci->load->model(WxDao::class);
        $this->wxDao = new WxDao();
        $ci->load->library(WeChatClient::class);
        $this->weChatClient = new WeChatClient();
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
            list($error, $data) = $this->weChatClient->httpGet($url, $query);
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
        $accessToken = $this->wxDao->getAccessToken();
        if (!$accessToken) {
            $url = WECHAT_API_CGIBIN . 'token';
            $query = array(
                'grant_type' => 'client_credential',
                'appid' => $this->appId,
                'secret' => $this->appSecret
            );
            list($error, $data) = $this->weChatClient->httpGet($url, $query);
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

    function wechatHttpPost($path, $data)
    {
        $accessToken = $this->getAccessToken();
        $url = WECHAT_API_CGIBIN . $path;
        $query = array(
            'access_token' => $accessToken
        );
        return $this->weChatClient->httpPost($url, $query, $data);
    }

    function fetchWxappSessionKey($code)
    {
        if (isDebug()) {
            $obj = new Stdclass;
            $obj->openid = 'o72gJ0ds_nwh2pxkQ1iexCc_fwZU';
            $obj->session_key = 'zBpz9ba/shj8Lfmur7Qt9g==';
            $obj->expires_in = 2592000;
            $result = array(null, $obj);
            return $result;
        }
        $url = WECHAT_API_BASE . 'sns/jscode2session';
        $data = array(
            'appid' => WXAPP_APPID,
            'secret' => WXAPP_SECRET,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        );
        return $this->weChatClient->httpGet($url, $data);
    }

    function wechatHttpGet($path, $params = array())
    {
        $accessToken = $this->getAccessToken();
        $url = WECHAT_API_CGIBIN . $path;
        $params['access_token'] = $accessToken;
        return $this->weChatClient->httpGet($url, $params);
    }

    function createMenu()
    {
        $data = array(
            'button' => array(
                array(
                    'type' => 'view',
                    'name' => '最新直播',
                    'url' => 'http://m.quzhiboapp.com/?liveId=0'
                ),
                array(
                    'name' => '历史热门',
                    'sub_button' => array(
                        array(
                            'type' => 'view',
                            'name' => '唐巧谈iOS安全',
                            'url' => 'http://m.quzhiboapp.com/?liveId=252'
                        ),
                        array(
                            'type' => 'view',
                            'name' => 'iDev苹果大会',
                            'url' => 'http://m.quzhiboapp.com/?liveId=180'
                        ),
                        array(
                            'type' => 'view',
                            'name' => '孙源谈iOS面试',
                            'url' => 'http://m.quzhiboapp.com/?liveId=115'
                        ),
                        array(
                            'type' => 'view',
                            'name' => '大胡子讲产品',
                            'url' => 'http://m.quzhiboapp.com/?liveId=255'
                        ),
                        array(
                            'type' => 'view',
                            'name' => '张星宇谈高效用Mac键盘',
                            'url' => 'http://m.quzhiboapp.com/?liveId=193'
                        )
                    ),
                ),
                array(
                    'name' => '创业故事',
                    'sub_button' => array(
                        array(
                            'type' => 'view',
                            'name' => '95年创始人成长经历',
                            'url' => 'http://mp.weixin.qq.com/s/KVr_s8bWOBfeexCmhHBpIw'
                        ),
                        array(
                            'type' => 'view',
                            'name' => '趣直播发布会',
                            'url' => 'http://mp.weixin.qq.com/s/-ebQBwpCT0YWs-0rM0fB2w'
                        ),
                        array(
                            'type' => 'view',
                            'name' => '趣直播首战告捷',
                            'url' => 'http://mp.weixin.qq.com/s/UvBP3Y9Aw3x0g0bFGa5mdQ'
                        ),
                        array(
                            'type' => 'view',
                            'name' => '上线两个月感想',
                            'url' => 'http://mp.weixin.qq.com/s/5ww7zVtXnKWfL2mCaBN8Og'
                        ),
                        array(
                            'type' => 'view',
                            'name' => '创业一年心得',
                            'url' => 'http://mp.weixin.qq.com/s/TwGrRUclPmIxoRu55zLmDg'
                        )
                    )
                )
            )
        );
        return $this->wechatHttpPost('menu/create', $data);
    }

    function httpGetUserInfo($accessToken, $openId)
    {
        $url = WECHAT_API_BASE . 'sns/userinfo';
        $query = array(
            'access_token' => $accessToken,
            'openid' => $openId,
            'lang' => 'zh_CN'
        );
        return $this->weChatClient->httpGet($url, $query);
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
        return $this->weChatClient->httpGet($url, $query);
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
        return $this->weChatClient->httpGet($url, $query);
    }

    function httpGetUnionId($accessToken, $openId)
    {
        $url = WECHAT_API_BASE . 'sns/userinfo';
        $data = array(
            'access_token' => $accessToken,
            'openid' => $openId
        );
        return $this->weChatClient->httpGet($url, $data);
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
        $data = array(
            'expire_seconds' => 60 * 60,
            'action_name' => 'QR_LIMIT_STR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_str' => json_encode($sceneData)
                )
            )
        );
        return $this->wechatHttpPost('qrcode/create', $data);
    }

}

