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

    function callback_get()
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

    private function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml,
            'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    public function arrayToXml($values)
    {
        if (!is_array($values)
            || count($values) <= 0
        ) {
            throw new Exception("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    private function textReply($fromUsername, $toUsername, $content)
    {
        $time = time();
        $data = array(
            KEY_FROM_USER_NAME => $fromUsername,
            KEY_TO_USER_NAME => $toUsername,
            KEY_CREATE_TIME => $time,
            KEY_MSG_TYPE => 'text',
            KEY_CONTENT => $content
        );
        return $data;
    }

    function replyToWeChat($data)
    {
        logInfo("wechat reply: " . json_encode($data));
        $xml = $this->arrayToXml($data);
        echo $xml;
    }


    function callback_post()
    {
        $xmlStr = file_get_contents('php://input');

        logInfo("wechat post str:\n $xmlStr");

        //extract post data
        if (!empty($xmlStr)) {

            $postObj = $this->xmlToArray($xmlStr);
            $fromUsername = $postObj[KEY_FROM_USER_NAME];
            $toUsername = $postObj[KEY_TO_USER_NAME];
            $createTime = $postObj[KEY_CREATE_TIME];
            $msgType = $postObj[KEY_MSG_TYPE];
            if ($msgType == MSG_TYPE_TEXT) {
                $keyword = trim($postObj[KEY_CONTENT]);
                $contentStr = '您的消息我们已经收到。';
                $textReply = $this->textReply($toUsername, $fromUsername, $contentStr);
                $this->replyToWeChat($textReply);
            } else if ($msgType == MSG_TYPE_EVENT) {
                $event = $postObj[KEY_EVENT];
                if ($event == EVENT_SUBSCRIBE) {
                    $userId = $this->snsUserDao->getUserIdByOpenId($fromUsername);
                    if ($userId) {
                        $this->userDao->updateSubscribe($userId, 1);
                    }
                    $contentStr = WECHAT_WELCOME_WORD;
                    $welcomeReply = $this->textReply($toUsername, $fromUsername, $contentStr);
                    $this->replyToWeChat($welcomeReply);
                } else if ($event == EVENT_UNSUBSCRIBE) {
                    $userId = $this->snsUserDao->getUserIdByOpenId($fromUsername);
                    if ($userId) {
                        $this->userDao->updateSubscribe($userId, 0);
                    }
                    logInfo("unsubscribe event");
                } else if ($event == EVENT_VIEW) {
                    logInfo("event view");
                }
            }
        } else {
            echo '';
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

    private function queryIsSubscribe($userId)
    {
        $openId = $this->snsUserDao->getOpenIdByUserId($userId);
        if (!$openId) {
            return array(ERROR_SNS_USER_NOT_EXISTS, 0);
        }
        return $this->jsSdk->queryIsSubscribeByOpenId($openId);
    }

    function isSubscribe_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_USER_ID))) {
            return;
        }
        $userId = $this->get(KEY_USER_ID);
        list($error, $isSubscribe) = $this->queryIsSubscribe($userId);
        if ($error) {
            $this->failure($error);
            return;
        }
        $this->succeed($isSubscribe);
    }

    private function createMenu()
    {
        $accessToken = $this->jsSdk->getAccessToken();
        $url = WECHAT_API_CGIBIN . 'menu/create?access_token='
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
