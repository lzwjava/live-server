<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/5/16
 * Time: 5:26 PM
 */
class Wechat extends BaseController
{
    public $jsSdk;
    public $snsUserDao;
    public $userDao;

    function __construct()
    {
        parent::__construct();
        $this->load->library(JSSDK::class);
        $this->jsSdk = new JSSDK(WECHAT_APP_ID, WECHAT_APP_SECRET);
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
    }

    function sign_get()
    {
        $this->succeed($this->jsSdk->getSignPackage());
    }

    function register_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_CODE))) ;
        $code = $this->post(KEY_CODE);
        $tokenResult = $this->httpGetAccessToken($code);
        if ($tokenResult->error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $tokenResult->error);
            return;
        }
        $respData = $tokenResult->data;
        $snsUser = $this->snsUserDao->getSnsUser($respData->openid, PLATFORM_WECHAT);
        if ($snsUser != null) {
            if ($snsUser->userId != 0) {
                // 已绑定手机
                $user = $this->userDao->setLoginByUserId($snsUser->userId);
                $this->succeed($user);
                return;
            } else {
                $this->succeed();
            }
        } else {
            $userResp = $this->httpGetUserInfo($respData->access_token, $respData->openid);
            if ($userResp->error) {
                $this->failure(ERROR_USER_INFO_FAILED, $userResp->error);
                return;
            }
            $weUser = $userResp->data;
            $id = $this->snsUserDao->addSnsUser($weUser->openid, $weUser->nickname,
                $weUser->headimgurl, PLATFORM_WECHAT);
            if (!$id) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
            $this->succeed();
        }
    }

    function httpGetUserInfo($accessToken, $openId)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='
            . $accessToken . '&openid=' . $openId . '&lang=zh_CN';
        $resp = $this->jsSdk->httpGet($url);
        return $this->parseResponse($resp);
    }

    private function parseResponse($respStr)
    {
        $result = new StdClass;
        if ($respStr === false) {
            $result->error = 'network error';
            return $result;
        }
        $data = json_decode($respStr);
        if (isset($data->errcode)) {
            $result->error = $data->errmsg;
        } else {
            $result->error = null;
            $result->data = $data;
        }
        return $result;
    }

    function httpGetAccessToken($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . WECHAT_APP_ID . '&secret=' .
            WECHAT_APP_SECRET . '&grant_type=authorization_code&code=' . $code;
        $resp = $this->jsSdk->httpGet($url);
        return $this->parseResponse($resp);
    }

}
