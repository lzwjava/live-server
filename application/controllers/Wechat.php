<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/5/16
 * Time: 5:26 PM
 */

require_once(APPPATH . 'libraries/wxencrypt/WxBizDataCrypt.php');

class Wechat extends BaseController
{
    public $jsSdk;
    public $snsUserDao;
    public $userDao;
    public $wxPay;
    public $notify;
    public $liveDao;
    public $packetDao;

    /**@var WxDao */
    public $wxDao;

    /**@var WxSessionDao */
    public $wxSessionDao;

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
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(PacketDao::class);
        $this->packetDao = new PacketDao();
        $this->load->model(WxDao::class);
        $this->wxDao = new WxDao();
        $this->load->model(WxSessionDao::class);
        $this->wxSessionDao = new WxSessionDao();
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
                        $this->failure(ERROR_BIND_UNION_ID_TO_USER);
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
        $xml = $this->arrayToXml($data);
        echo $xml;
    }

    private function extraWordFromEventKey($eventKeyStr)
    {
        $sceneData = json_decode($eventKeyStr);
        $extraWord = '';
        if ($sceneData) {
            if ($sceneData->type == 'live') {
                $liveId = $sceneData->liveId;
                $live = $this->liveDao->getLive($liveId);
                $extraWord = sprintf(WECHAT_LIVE_WORD, $liveId, $live->subject);
            } else if ($sceneData->type == 'packet') {
                $packetId = $sceneData->packetId;
                $packet = $this->packetDao->getPacketById($packetId);
                $extraWord = sprintf(WECHAT_PACKET_WORD,
                    $packet->user->username, $packetId);
            }
        }
        return $extraWord;
    }

    function callback_post()
    {
        $xmlStr = file_get_contents('php://input');

        //extract post data
        if (!empty($xmlStr)) {
            $postObj = $this->xmlToArray($xmlStr);

            logInfo("wechat callback:\n " . json_encode($postObj));

            $fromUsername = $postObj[KEY_FROM_USER_NAME];
            $toUsername = $postObj[KEY_TO_USER_NAME];
            $createTime = $postObj[KEY_CREATE_TIME];
            $msgType = $postObj[KEY_MSG_TYPE];
            if ($msgType == MSG_TYPE_TEXT) {
                $keyword = trim($postObj[KEY_CONTENT]);
                $contentStr = '如果有任何问题请联系创始人微信 lzwjava 。';
                $textReply = $this->textReply($toUsername, $fromUsername, $contentStr);
                $this->replyToWeChat($textReply);
            } else if ($msgType == MSG_TYPE_EVENT) {
                $event = $postObj[KEY_EVENT];
                $eventKey = $postObj[KEY_EVENT_KEY];
                if (gettype($eventKey) == 'array') {
                    $eventKey = '';
                }
                if ($event == EVENT_SUBSCRIBE) {
                    $userId = $this->snsUserDao->getUserIdByOpenId($fromUsername);
                    if ($userId) {
                        $this->userDao->updateSubscribe($userId, 1);
                    }

                    $extraWord = '';
                    if (substr($eventKey, 0, 8) == 'qrscene_') {
                        $sceneStr = substr($eventKey, 8, strlen($eventKey));
                        $extraWord = $this->extraWordFromEventKey($sceneStr);
                    }
                    $contentStr = sprintf(WECHAT_WELCOME_WORD, $extraWord);
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
                } else if ($event == EVENT_SCAN) {
                    $userId = $this->snsUserDao->getUserIdByOpenId($fromUsername);
                    list($error, $theSubscribe) = $this->jsSdk->queryIsSubscribeByOpenId($fromUsername);
                    if (!$error) {
                        $this->userDao->updateSubscribe($userId, $theSubscribe);
                    }

                    $extraWord = $this->extraWordFromEventKey($eventKey);
                    $welcomeReply = $this->textReply($toUsername, $fromUsername, $extraWord);
                    $this->replyToWeChat($welcomeReply);
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

    function fixAllSubscribe_get()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $users = $this->userDao->findAllUsers();
        $subscribeCount = 0;
        for ($i = 0; $i < count($users); $i++) {
            $user = $users[$i];
            if ($user->wechatSubscribe == 0) {
                list($error, $subscribe) = $this->queryIsSubscribe($user->userId);
                if (!$error) {
                    $this->userDao->updateSubscribe($user->userId, $subscribe);
                    if ($subscribe) {
                        $subscribeCount++;
                    }
                }
            }
            if ($i % 100 == 0) {
                logInfo("handle $i subscribeCount:" . $subscribeCount);
            }
        }
        logInfo("finish subscribeCount:" . $subscribeCount);
        $this->succeed(array('subscribeCount' => $subscribeCount, 'total' => count($users)));
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
                ),
                array(
                    'type' => 'view',
                    'name' => '发布会',
                    'url' => 'http://mp.weixin.qq.com/s/-ebQBwpCT0YWs-0rM0fB2w'
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

    function qrcode_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_TYPE))) {
            return;
        }
        $type = $this->get(KEY_TYPE);
        $liveId = $this->toNumber($this->get(KEY_LIVE_ID));
        $packetId = $this->get(KEY_PACKET_ID);
        $data = null;
        if ($type == 'live') {
            $data = array(KEY_TYPE => $type, KEY_LIVE_ID => $liveId);
        } else if ($type == 'packet') {
            $data = array(KEY_TYPE => $type, KEY_PACKET_ID => $packetId);
        }
        list($error, $data) = $this->jsSdk->genQrcode($data);
        if ($error) {
            $this->failure(ERROR_WECHAT, $error);
            return;
        }
        $this->succeed($data);
    }

    function login_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_CODE))) {
            return;
        }
        $code = $this->post(KEY_CODE);
        list($error, $data) = $this->jsSdk->fetchWxappSessionKey($code);
        if ($error) {
            $this->failure(ERROR_WECHAT, $error);
            return;
        }
        $thirdSession = getToken(48);
        $ok = $this->wxSessionDao->setOpenIdAndSessionKey($thirdSession, $data);
        if (!$ok) {
            $this->failure(ERROR_REDIS_WRONG);
            return;
        }
        $this->succeed(array(KEY_THIRD_SESSION => $thirdSession));
    }

    private function checkAppSignWrong($rawData, $thirdSessionData, $signature)
    {
        if (isDebug()) {
            return false;
        }
        $signStr = $rawData . $thirdSessionData->session_key;
        if (sha1($signStr) != $signature) {
            return true;
        }
        return false;
    }

    function register_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_RAW_DATA,
            KEY_SIGNATURE, KEY_IV, KEY_ENCRYPTED_DATA, KEY_THIRD_SESSION))
        ) {
            return;
        }
        $iv = $this->post(KEY_IV);
        $encryptedData = $this->post(KEY_ENCRYPTED_DATA);
        $thirdSession = $this->post(KEY_THIRD_SESSION);

        $thirdSessionData = $this->wxSessionDao->getOpenIdAndSessionKey($thirdSession);
        if (!$thirdSessionData) {
            $this->failure(ERROR_SESSION_KEY_NOT_EXISTS);
            return;
        }
        $rawData = $this->post(KEY_RAW_DATA);
        $signature = $this->post(KEY_SIGNATURE);
        if ($this->checkAppSignWrong($rawData, $thirdSessionData, $signature)) {
            $this->failure(ERROR_WX_SIGN);
            return;
        }

        $pc = new WXBizDataCrypt(WXAPP_APPID, $thirdSessionData->session_key);
        $data = '';
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode != 0) {
            logInfo("decrypt errCode " . $errCode);
            $this->failure(ERROR_WX_ENCRYPT);
            return;
        }
        $this->succeed($data);
    }

}
