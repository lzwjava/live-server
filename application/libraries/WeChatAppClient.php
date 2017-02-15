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

    /** @var SnsUserDao */
    public $snsUserDao;

    /** @var UserDao */
    public $userDao;

    private $appId;
    private $appSecret;

    function __construct()
    {
        $this->appId = WXAPP_APPID;
        $this->appSecret = WXAPP_SECRET;

        $ci = get_instance();
        $ci->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $ci->load->model(UserDao::class);
        $this->userDao = new UserDao();
        $ci->load->library(WeChatClient::class);
        $this->weChatClient = new WeChatClient();
        $ci->load->model(WxAppDao::class);
        $this->wxAppDao = new WxAppDao();
    }

    private function getAccessToken()
    {
        if (isDebug()) {
            return array(null, TMP_WXAPP_ACCESS_TOKEN);
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

    private function wechatHttpPost($path, $data)
    {
        list($error, $accessToken) = $this->getAccessToken();
        if ($error) {
            return array($error, null);
        }
        $url = WECHAT_API_CGIBIN . $path;
        $query = array(
            'access_token' => $accessToken
        );
        return $this->weChatClient->httpPost($url, $query, $data);
    }

    private function notifyByWeChat($user, $tempId, $page, $formId, $tmplData)
    {
        if (!$user->unionId) {
            logInfo("the user $user->username do not have unionId fail send wechat msg");
            return false;
        }
        $snsUser = $this->snsUserDao->getWxAppSnsUser($user->unionId);
        $data = array(
            'touser' => $snsUser->openId,
            'template_id' => $tempId,
            'page' => $page,
            'form_id' => $formId,
            'data' => $tmplData
        );
        list($error, $data) = $this->wechatHttpPost('message/wxopen/template/send', $data);
        if ($error) {
            logInfo('wechat notified failed user:' . $user->userId . ' error: ' . $error);
            return false;
        }
        return true;
    }

    function notifyLiveStart($userId, $prepayId, $live)
    {
        $user = $this->userDao->findUserById($userId);
        $word = $user->username . '，您参与的直播即将开始啦';
        $tmplData = array(
            'keyword1' => array(
                'value' => $live->subject,
                'color' => '#173177',
            ),
            'keyword2' => array(
                'value' => $live->owner->username,
                'color' => '#000',
            ),
            'keyword3' => array(
                'value' => $live->planTs,
                'color' => '#173177',
            ),
            'keyword4' => array(
                'value' => $word,
                'color' => '#000',
            )
        );
        $page = 'pages/live/live?liveId=' . $live->liveId;
        return $this->notifyByWeChat($user, 'LlaxiBTOa7ZmLx8KIZG-S8xAXOEl0OKr5q992jGGkII',
            $page, $prepayId, $tmplData);
    }

}
