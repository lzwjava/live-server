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
    public $wxPay;
    public $notify;

    function __construct()
    {
        parent::__construct();
        $this->load->library(JSSDK::class);
        $this->jsSdk = new JSSDK(WECHAT_APP_ID, WECHAT_APP_SECRET);
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
        $this->load->library('wx/' . WxPay::class);
        $this->wxPay = new WxPay();
        $this->load->library('wx/' . WxPayCallback::class);
        $this->notify = new WxPayCallback();
    }

    function sign_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_URL))) {
            return;
        }
        $url = urldecode($this->get('url'));
        //logInfo("sign url:" . $url);
        $this->succeed($this->jsSdk->getSignPackage($url));
    }

    private function httpGetUserInfo($accessToken, $openId)
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
            $result->errorcode = $data->errcode;
        } else {
            $result->error = null;
            $result->data = $data;
        }
        return $result;
    }

    private function baseHttpGetAccessToken($code, $wechatAppId, $wechatSecret)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $wechatAppId .
            '&secret=' . $wechatSecret . '&grant_type=authorization_code&code=' . $code;
        $resp = $this->jsSdk->httpGet($url);
        return $this->parseResponse($resp);
    }

    private function httpGetUnionId($accessToken, $openId)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $accessToken .
            '&openid=' . $openId;
        $resp = $this->jsSdk->httpGet($url);
        return $this->parseResponse($resp);
    }

    private function getUnionId($accessToken, $openId)
    {
        $unionResult = $this->httpGetUnionId($accessToken, $openId);
        if (!$unionResult->error && $unionResult->data->unionid) {
            return $unionResult->data->unionid;
        } else {
            logInfo("failed union result: " . json_encode($unionResult));
            return null;
        }
    }

    private function httpGetAccessToken($code)
    {
        return $this->baseHttpGetAccessToken($code, WECHAT_APP_ID, WECHAT_APP_SECRET);
    }

    private function webHttpGetAccessToken($code)
    {
        return $this->baseHttpGetAccessToken($code, WEB_WECHAT_APP_ID, WEB_WECHAT_APP_SECRET);
    }

    function oauth_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE))) {
            return;
        };
        $code = $this->get(KEY_CODE);
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
                $this->failure(ERROR_WECHAT_ALREADY_REGISTER);
                return;
            }
            $this->succeed($snsUser);
        } else {
            $userResp = $this->httpGetUserInfo($respData->access_token, $respData->openid);
            if ($userResp->error) {
                $this->failure(ERROR_USER_INFO_FAILED, $userResp->error);
                return;
            }
            $weUser = $userResp->data;
            $id = $this->snsUserDao->addSnsUser($weUser->openid, $weUser->nickname,
                $weUser->headimgurl, PLATFORM_WECHAT, $weUser->unionid);
            if (!$id) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
            $snsUser = $this->snsUserDao->getSnsUser($weUser->openid, PLATFORM_WECHAT);
            $this->succeed($snsUser);
        }
    }

    function silentOauth_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE))) {
            return;
        }
        $code = $this->get(KEY_CODE);
        $tokenResult = $this->httpGetAccessToken($code);
        if ($tokenResult->error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $tokenResult->error);
            return;
        }
        $respData = $tokenResult->data;
        $snsUser = $this->snsUserDao->getSnsUser($respData->openid, PLATFORM_WECHAT);
        $unionId = null;
        if ($snsUser != null) {
            if (!$snsUser->unionId) {
                $unionId = $this->getUnionId($respData->access_token, $respData->openid);
                if (!$unionId) {
                    $this->failure(ERROR_GET_UNION_ID);
                    return;
                }
                $binds = $this->snsUserDao->bindUnionIdToSnsUser($respData->openid,
                    PLATFORM_WECHAT, $unionId);
                if (!$binds) {
                    $this->failure(ERROR_BIND_UNION_ID);
                    return;
                }
            } else {
                $unionId = $snsUser->unionId;
            }
            if ($snsUser->userId != 0) {
                $unionUser = $this->userDao->findUserByUnionId($unionId);
                if (!$unionUser) {
                    $userBinds = $this->userDao->bindUnionIdToUser($snsUser->userId, $unionId);
                    if (!$userBinds) {
                        $this->failure(ERROR_SQL_WRONG);
                        return;
                    }
                }
            }
        }

        if ($unionId) {
            $findUser = $this->userDao->findUserByUnionId($unionId);
            if (!$findUser) {
                $this->failure(ERROR_UNION_ID_USER_NOT_EXISTS);
                return;
            }
            $user = $this->userDao->setLoginByUserId($findUser->userId);
            $this->succeed($user);
            return;
        }

        $this->succeed();
    }

    function webOauth_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE))) {
            return;
        }
        $code = $this->get(KEY_CODE);
        $tokenResult = $this->webHttpGetAccessToken($code);
        if ($tokenResult->error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $tokenResult->error);
            return;
        }
        $respData = $tokenResult->data;
        $unionResp = $this->httpGetUnionId($respData->access_token, $respData->openid);
        if ($unionResp->error) {
            $this->failure(ERROR_GET_USER_INFO);
            return;
        }
        $unionResult = $unionResp->data;
        // logInfo("union data:" . json_encode($unionResp));
        $unionId = $unionResult->unionid;
        $snsUser = $this->snsUserDao->getSnsUserByUnionId($unionId);
        if (!$snsUser || !$snsUser->userId) {
            $this->failure(ERROR_SNS_USER_NOT_EXISTS);
            return;
        }
        $user = $this->userDao->setLoginByUserId($snsUser->userId);
        $this->succeed($user);
    }

    function wxpayNotify_post()
    {
        $this->notify->Handle(false);
    }

}
