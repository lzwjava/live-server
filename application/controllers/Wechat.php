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
    public $stateDao;

    function __construct()
    {
        parent::__construct();
        $this->load->library(JSSDK::class);
        $this->jsSdk = new JSSDK(WECHAT_APP_ID, WECHAT_APP_SECRET);
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
        $this->load->model(StateDao::class);
        $this->stateDao = new StateDao();
    }

    function sign_get()
    {
        $this->succeed($this->jsSdk->getSignPackage());
    }

    function register_post()
    {

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

    function oauth_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE, KEY_STATE))) {
            return;
        };
        $code = $this->get(KEY_CODE);
        $hash = $this->get(KEY_STATE);
        $state = $this->stateDao->getState($hash);
        if (!$state) {
            $this->failure(ERROR_ILLEGAL_REQUEST);
            return;
        }
        $tokenResult = $this->httpGetAccessToken($code);
        if ($tokenResult->error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $tokenResult->error);
            return;
        }
        $respData = $tokenResult->data;
        $snsUser = $this->snsUserDao->getSnsUser($respData->openid, PLATFORM_WECHAT);
        $host = $this->getMobileHost();
        $redirectHeader = 'Location: http://' . $host . '/#register?liveId='
            . $state->liveId . '&openId=' . $respData->openid;
        if ($snsUser != null) {
            if ($snsUser->userId != 0) {
                $this->failure(ERROR_WECHAT_ALREADY_REGISTER);
                return;
            } else {
                header($redirectHeader);
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
            header($redirectHeader);
        }
    }

    function silentOauth_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE, KEY_STATE))) {
            return;
        }
        $code = $this->get(KEY_CODE);
        $hash = $this->get(KEY_STATE);
        $state = $this->stateDao->getState($hash);
        if (!$state) {
            $this->failure(ERROR_ILLEGAL_REQUEST);
            return;
        }
        $tokenResult = $this->httpGetAccessToken($code);
        if ($tokenResult->error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $tokenResult->error);
            return;
        }
        $respData = $tokenResult->data;
        $snsUser = $this->snsUserDao->getSnsUser($respData->openid, PLATFORM_WECHAT);
        if ($snsUser != null) {
            if ($snsUser->userId != 0) {
                $this->userDao->setLoginByUserId($snsUser->userId);
            }
        }
        $host = $this->getMobileHost();
        header('Location: http://' . $host . '/#intro/' . $state->liveId);
    }

    private function getMobileHost()
    {
        if (true) {
            return 'localhost:9060';
        } else {
            return 'm.quzhiboapp.com';
        }
    }

}
