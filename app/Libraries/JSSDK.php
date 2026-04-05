<?php

namespace App\Libraries;

use App\Models\WxDao;
use App\Libraries\WeChatClient;

/**
 * JSSDK - WeChat JSSDK integration
 * CI4-compatible version (no get_instance(), no $this->load)
 */
class JSSDK
{
    private ?string $appId;
    private ?string $appSecret;
    private ?WxDao $wxDao = null;
    private ?WeChatClient $weChatClient = null;

    public function __construct(?string $appId = null, ?string $appSecret = null)
    {
        $this->appId = $appId ?? env('WECHAT_APP_ID');
        $this->appSecret = $appSecret ?? env('WECHAT_APP_SECRET');
    }

    private function getWxDao(): WxDao
    {
        if ($this->wxDao === null) {
            $this->wxDao = new WxDao();
        }
        return $this->wxDao;
    }

    private function getWeChatClient(): WeChatClient
    {
        if ($this->weChatClient === null) {
            $this->weChatClient = new WeChatClient();
        }
        return $this->weChatClient;
    }

    public function getSignPackage(string $url): array
    {
        $jsapiTicket = $this->getJsApiTicket();
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);

        return [
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
        ];
    }

    private function createNonceStr(int $length = 16): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    private function getJsApiTicket()
    {
        if (env('WECHAT_DEBUG') === 'true') {
            return env('TMP_WECHAT_JSAPI_TICKET', 'temp_jsapi_ticket');
        }
        $wxDao = $this->getWxDao();
        $ticket = $wxDao->getJSApiTicket();
        if (!$ticket) {
            $accessToken = $this->getAccessToken();
            $url = env('WECHAT_API_CGIBIN') . 'ticket/getticket';
            $query = ['type' => 'jsapi', 'access_token' => $accessToken];
            [$error, $data] = $this->getWeChatClient()->httpGet($url, $query);
            if (!$error && $data) {
                $ticket = $data->ticket ?? null;
                if ($ticket) {
                    $wxDao->setJSApiTicket($ticket, $data->expires_in ?? 7200);
                }
            }
        }
        return $ticket ?: '';
    }

    public function getAccessToken(): string
    {
        if (env('WECHAT_DEBUG') === 'true') {
            return env('TMP_WECHAT_ACCESS_TOKEN', 'temp_access_token');
        }
        $wxDao = $this->getWxDao();
        $accessToken = $wxDao->getAccessToken();
        if (!$accessToken) {
            $url = env('WECHAT_API_CGIBIN') . 'token';
            $query = [
                'grant_type' => 'client_credential',
                'appid'      => $this->appId,
                'secret'     => $this->appSecret,
            ];
            [$error, $data] = $this->getWeChatClient()->httpGet($url, $query);
            if (!$error && $data) {
                $accessToken = $data->access_token ?? '';
                if ($accessToken) {
                    $wxDao->setAccessToken($accessToken, $data->expires_in ?? 7200);
                }
            }
        }
        return $accessToken ?: '';
    }

    public function wechatHttpPost(string $path, array $data, ?array $query = null): array
    {
        $accessToken = $this->getAccessToken();
        $baseUrl = env('WECHAT_API_CGIBIN') . $path;
        $params = $query ?? [];
        $params['access_token'] = $accessToken;
        return $this->getWeChatClient()->httpPost($baseUrl, $params);
    }

    public function wechatHttpGet(string $path, array $params = []): array
    {
        $accessToken = $this->getAccessToken();
        $baseUrl = env('WECHAT_API_CGIBIN') . $path;
        $params['access_token'] = $accessToken;
        return $this->getWeChatClient()->httpGet($baseUrl, $params);
    }

    public function fetchWxappSessionKey(string $code): array
    {
        if (env('WECHAT_DEBUG') === 'true') {
            $obj = new \stdClass();
            $obj->openid = 'o72gJ0ds_nwh2pxkQ1iexCc_fwZU';
            $obj->session_key = 'zBpz9ba/shj8Lfmur7Qt9g==';
            $obj->expires_in = 2592000;
            return [null, $obj];
        }
        $url = env('WECHAT_API_BASE') . 'sns/jscode2session';
        $data = [
            'appid'      => env('WXAPP_APPID'),
            'secret'     => env('WXAPP_SECRET'),
            'js_code'    => $code,
            'grant_type' => 'authorization_code',
        ];
        return $this->getWeChatClient()->httpGet($url, $data);
    }

    public function createMenu(): array
    {
        $data = [
            'button' => [
                [
                    'type' => 'view',
                    'name' => '最新直播',
                    'url'  => 'http://m.quzhiboapp.com/?liveId=0',
                ],
                [
                    'type' => 'view',
                    'name' => '个人主页',
                    'url'  => 'http://m.quzhiboapp.com/#profile',
                ],
                [
                    'name'       => '关于',
                    'sub_button' => [
                        [
                            'type'   => 'view',
                            'name'   => '趣直播融资',
                            'url'    => 'http://mp.weixin.qq.com/s/g_jeEG8ee6kF2lL6wzUPwg',
                        ],
                        [
                            'type'    => 'media_id',
                            'name'    => '创始人微信',
                            'media_id' => '4JUYm2nOjNSMcz26AidpLSJGCOknYQcUu1Lw8PlWMxE',
                        ],
                    ],
                ],
            ],
        ];
        return $this->wechatHttpPost('menu/create', $data);
    }

    public function httpGetUserInfo(string $accessToken, string $openId): array
    {
        $url = env('WECHAT_API_BASE') . 'sns/userinfo';
        $query = [
            'access_token' => $accessToken,
            'openid'      => $openId,
            'lang'        => 'zh_CN',
        ];
        return $this->getWeChatClient()->httpGet($url, $query);
    }

    public function queryIsSubscribeByOpenId(string $openId): array
    {
        $accessToken = $this->getAccessToken();
        [$error, $weUser] = $this->httpGetUserInfoByPlatform($accessToken, $openId);
        if ($error) {
            if (function_exists('logInfo')) {
                logInfo("fetch user info error " . $error);
            }
            return [defined('ERROR_USER_INFO_FAILED') ? ERROR_USER_INFO_FAILED : 'ERROR_USER_INFO_FAILED', 0];
        }
        return [null, $weUser->subscribe ?? 0];
    }

    private function httpGetUserInfoByPlatform(string $accessToken, string $openId): array
    {
        $url = env('WECHAT_API_CGIBIN') . 'user/info';
        $query = [
            'access_token' => $accessToken,
            'openid'      => $openId,
            'lang'        => 'zh_CN',
        ];
        return $this->getWeChatClient()->httpGet($url, $query);
    }

    private function baseHttpGetAccessToken(string $code, string $wechatAppId, string $wechatSecret): array
    {
        $url = env('WECHAT_API_BASE') . 'sns/oauth2/access_token';
        $query = [
            'appid'      => $wechatAppId,
            'secret'     => $wechatSecret,
            'grant_type' => 'authorization_code',
            'code'       => $code,
        ];
        return $this->getWeChatClient()->httpGet($url, $query);
    }

    public function httpGetAccessToken(string $code): array
    {
        return $this->baseHttpGetAccessToken($code, env('WECHAT_APP_ID') ?? '', env('WECHAT_APP_SECRET') ?? '');
    }

    public function webHttpGetAccessToken(string $code): array
    {
        return $this->baseHttpGetAccessToken($code, env('WEB_WECHAT_APP_ID') ?? '', env('WEB_WECHAT_APP_SECRET') ?? '');
    }

    public function appHttpGetAccessToken(string $code): array
    {
        return $this->baseHttpGetAccessToken($code, env('MOBILE_WECHAT_APP_ID') ?? '', env('MOBILE_WECHAT_APP_SECRET') ?? '');
    }

    public function genQrcode(array $sceneData): array
    {
        $data = [
            'expire_seconds' => 3600,
            'action_name'    => 'QR_LIMIT_STR_SCENE',
            'action_info'    => [
                'scene' => [
                    'scene_str' => json_encode($sceneData),
                ],
            ],
        ];
        return $this->wechatHttpPost('qrcode/create', $data);
    }
}
