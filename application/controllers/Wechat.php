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


    function oauth_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE))) {
            return;
        };
        $code = $this->get(KEY_CODE);
        list($error, $respData) = $this->jsSdk->httpGetAccessToken($code);
        if ($error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $error);
            return;
        }
        $snsUser = $this->snsUserDao->getSnsUser($respData->openid, PLATFORM_WECHAT);
        if ($snsUser != null) {
            if ($snsUser->userId != 0) {
                $this->userDao->setLoginByUserId($snsUser->userId);
                $this->failure(ERROR_WECHAT_ALREADY_REGISTER);
                return;
            }
            $this->succeed($snsUser);
        } else {
            list($error, $weUser) = $this->jsSdk->httpGetUserInfo($respData->access_token, $respData->openid);
            if ($error) {
                $this->failure(ERROR_USER_INFO_FAILED, $error);
                return;
            }
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
        list($error, $respData) = $this->jsSdk->httpGetAccessToken($code);
        if ($error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $error);
            return;
        }
        $snsUser = $this->snsUserDao->getSnsUser($respData->openid, PLATFORM_WECHAT);
        $unionId = null;
        if ($snsUser != null) {
            if (!$snsUser->unionId) {
                list($unionId, $errcode) = $this->jsSdk->getUnionId($respData->access_token, $respData->openid);
                if ($errcode == 48001) {
                    // api unauthorized
                    $this->succeed();
                    return;
                }
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
            if ($findUser) {
                $user = $this->userDao->setLoginByUserId($findUser->userId);
                $this->succeed($user);
                return;
            } else {
                // 有的用户微信登录了,但没绑定手机
            }
        }

        $this->succeed();
    }

    function webOauth_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE))) {
            return;
        }
        $code = $this->get(KEY_CODE);
        list($error, $respData) = $this->jsSdk->webHttpGetAccessToken($code);
        if ($error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $error);
            return;
        }
        list($error, $unionResult) = $this->jsSdk->httpGetUnionId($respData->access_token, $respData->openid);
        if ($error) {
            $this->failure(ERROR_GET_UNION_ID, $error);
            return;
        }
        $unionId = $unionResult->unionid;
        $snsUser = $this->snsUserDao->getSnsUserByUnionId($unionId);
        if (!$snsUser) {
            $this->failure(ERROR_SNS_USER_NOT_EXISTS);
            return;
        }
        if (!$snsUser->userId) {
            $this->failure(ERROR_SNS_USER_ID_EMPTY);
            return;
        }
        $user = $this->userDao->setLoginByUserId($snsUser->userId);
        $this->succeed($user);
    }

    function bind_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($user->unionId) {
            $this->failure(ERROR_WECHAT_ALREADY_BIND);
            return;
        }
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_CODE))) {
            return;
        }
        $code = $this->get(KEY_CODE);
        list($error, $respData) = $this->jsSdk->appHttpGetAccessToken($code);
        if ($error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $error);
            return;
        }
        list($error, $unionResult) = $this->jsSdk->httpGetUnionId($respData->access_token, $respData->openid);
        if ($error) {
            $this->failure(ERROR_GET_USER_INFO);
            return;
        }
        $unionId = $unionResult->unionid;

        $snsUser = $this->snsUserDao->getSnsUser($unionResult->openid, PLATFORM_WECHAT_APP);
        if (!$snsUser) {
            $snsId = $this->snsUserDao->addSnsUser($unionResult->openid, $unionResult->nickname,
                $unionResult->headimgurl, PLATFORM_WECHAT_APP, $unionId, $user->userId);
            if (!$snsId) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
        } else {
            $this->snsUserDao->bindUser($unionResult->openid, PLATFORM_WECHAT_APP, $user->userId);
        }
        $ok = $this->userDao->bindUnionIdToUser($user->userId, $unionId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

    function wxpayNotify_post()
    {
        $this->notify->Handle(false);
    }

    function valid_get()
    {
        $echoStr = $this->get('echostr');

        logInfo("wechat valid get str: " . json_encode($this->get()));

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
        $this->succeed();
    }

    function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)) {

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
            if (!empty($keyword)) {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            } else {
                echo "Input something...";
            }

        } else {
            echo "";
            exit;
        }
    }

    function checkSignature()
    {
        $signature = $this->get('signature');
        $timestamp = $this->get('timestamp');
        $nonce = $this->get('nonce');

        $token = WECHAT_TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    function appOauth_get()
    {
        $code = $this->get(KEY_CODE);
        list($error, $respData) = $this->jsSdk->appHttpGetAccessToken($code);
        if ($error) {
            $this->failure(ERROR_GET_ACCESS_TOKEN, $error);
            return;
        }
        list($error, $unionResult) = $this->jsSdk->httpGetUnionId($respData->access_token, $respData->openid);
        if ($error) {
            $this->failure(ERROR_GET_USER_INFO);
            return;
        }
        $unionId = $unionResult->unionid;
        $user = $this->userDao->findUserByUnionId($unionId);
        if ($user) {
            $user = $this->userDao->setLoginByUserId($user->userId);
            $this->succeed(array(KEY_TYPE => OAUTH_RESULT_LOGIN, OAUTH_USER => $user));
            return;
        }

        $snsUser = $this->snsUserDao->getSnsUser($respData->openid, PLATFORM_WECHAT_APP);
        if ($snsUser) {
            $this->succeed(array(KEY_TYPE => OAUTH_RESULT_REGISTER, OAUTH_SNS_USER => $snsUser));
        } else {
            $id = $this->snsUserDao->addSnsUser($unionResult->openid, $unionResult->nickname,
                $unionResult->headimgurl, PLATFORM_WECHAT_APP, $unionId, 0);
            if (!$id) {
                $this->failure(ERROR_SQL_WRONG);
                return;
            }
            $snsUser = $this->snsUserDao->getSnsUser($unionResult->openid, PLATFORM_WECHAT_APP);
            $this->succeed(array(KEY_TYPE => OAUTH_RESULT_REGISTER, OAUTH_SNS_USER => $snsUser));
        }
    }

    function isSubscribe_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_USER_ID))) {
            return;
        }
        $userId = $this->get(KEY_USER_ID);
        $snsUser = $this->snsUserDao->getWeChatSnsUserByUserId($userId);
        if (!$snsUser) {
            $this->failure(ERROR_SNS_USER_NOT_EXISTS);
            return;
        }
        $accessToken = $this->jsSdk->getAccessToken();
        list($error, $weUser) = $this->jsSdk->httpGetUserInfoByPlatform($accessToken, $snsUser->openId);
        if ($error) {
            $this->failure(ERROR_USER_INFO_FAILED, $error);
        }
        $result = null;
        if ($weUser->subscribe) {
            $result = true;
        } else {
            $result = false;
        }
        $this->succeed($result);
    }

    private function createMenu()
    {
        $accessToken = $this->jsSdk->getAccessToken();
        $url = WECHAT_API_BASE . 'menu/create?access_token='
            . $accessToken;
        $data = array(
            'button' => array(
                array(
                    'type' => 'view',
                    'name' => '最新直播',
                    'url' => 'http://m.quzhiboapp.com/?liveId=0'
                )
            )
        );
        return $this->jsSdk->httpPost($url, $data);
    }

    private function getMenu()
    {
        return $this->jsSdk->wechatHttpGet('menu/get');
    }

    function menu_get()
    {
        list($error, $data) = $this->getMenu();
        if ($error) {
            $this->failure(ERROR_WECHAT, $error);
            return;
        }
        $this->succeed($data);
    }

    function createMenu_get()
    {
        list($error, $data) = $this->createMenu();
        if ($error) {
            $this->failure(ERROR_WECHAT, $error);
            return;
        }
        $this->succeed($data);
    }

}
