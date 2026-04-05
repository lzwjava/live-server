<?php
/**
 * WeChatAppClient - handles WeChat mini-program and web OAuth/login.
 * Originally global namespace; migrated to App\Controllers for CI4 classmap.
 */

namespace App\Controllers {

class WeChatAppClient
{
    private $appId;
    private $appSecret;
    private $wxAppId;
    private $wxAppSecret;

    public function __construct()
    {
        $this->appId = WECHAT_APP_ID;
        $this->appSecret = WECHAT_APP_SECRET;
        $this->wxAppId = WXAPP_APPID;
        $this->wxAppSecret = WXAPP_SECRET;

        $ci = get_instance();
        $ci->load->model('SnsUserDao');
        $ci->load->model('UserDao');
        $ci->load->model('WxAppDao');
        $ci->load->model('WxDao');
        $ci->load->model('WxSessionDao');
        $this->snsUserDao = $ci->snsuserdao;
        $this->userDao = $ci->userdao;
        $this->wxAppDao = $ci->wxappdao;
        $this->wxDao = $ci->wxdao;
        $this->wxSessionDao = $ci->wxsessiondao;
    }

    public function login($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appId}&secret={$this->appSecret}&js_code={$code}&grant_type=authorization_code";
        $ret = json_decode(file_get_contents($url), true);
        if (isset($ret['errcode'])) {
            throw new \Exception($ret['errmsg']);
        }
        return $ret;
    }

    public function getUserInfo($openId, $accessToken)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
        return json_decode(file_get_contents($url), true);
    }

    public function getUserByCode($code)
    {
        $session = $this->login($code);
        $openId = $session['openid'];
        $unionId = $session['unionid'] ?? null;
        $user = $this->snsUserDao->findByOpenId($openId, WECHAT);
        if (!empty($user)) {
            return $user;
        }
        if (empty($unionId)) {
            $unionId = $this->getUnionId($openId, $session['session_key']);
        }
        if (!empty($unionId)) {
            $user = $this->snsUserDao->findByUnionId($unionId, WECHAT);
            if (!empty($user)) {
                $this->snsUserDao->updateOpenIdByUnionId($unionId, WECHAT, $openId);
                return $user;
            }
        }
        return null;
    }

    private function getUnionId($openId, $sessionKey)
    {
        $appId = $this->wxAppId;
        $appSecret = $this->wxAppSecret;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
        $ret = json_decode(file_get_contents($url), true);
        $accessToken = $ret['access_token'];
        $url = "https://api.weixin.qq.com/wxa/getpaidunionid?access_token=$accessToken&openid=$openId";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $ret = json_decode($response, true);
        return $ret['unionid'] ?? null;
    }

    public function createUserByCode($code, $extraData = [])
    {
        $session = $this->login($code);
        $openId = $session['openid'];
        $unionId = $session['unionid'] ?? null;
        $user = $this->userDao->createUser();
        $this->snsUserDao->create([
            'user_id' => $user->userId,
            'type' => WECHAT,
            'open_id' => $openId,
            'union_id' => $unionId,
        ]);
        $this->userDao->update($user->userId, $extraData);
        return $this->userDao->findById($user->userId);
    }

    public function bind($userId, $code, $type)
    {
        if ($type == WXAPP) {
            $session = $this->wxAppLogin($code);
            $openId = $session['openid'];
            $unionId = $session['unionid'] ?? null;
        } else {
            $session = $this->login($code);
            $openId = $session['openid'];
            $unionId = $session['unionid'] ?? null;
        }
        $this->snsUserDao->deleteByUserId($userId);
        $this->snsUserDao->create([
            'user_id' => $userId,
            'type' => $type,
            'open_id' => $openId,
            'union_id' => $unionId,
        ]);
        return $this->userDao->findById($userId);
    }

    private function wxAppLogin($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->wxAppId}&secret={$this->wxAppSecret}&js_code={$code}&grant_type=authorization_code";
        $ret = json_decode(file_get_contents($url), true);
        if (isset($ret['errcode'])) {
            throw new \Exception($ret['errmsg']);
        }
        return $ret;
    }

    public function getUserInfoByWxApp($code)
    {
        $session = $this->wxAppLogin($code);
        $openId = $session['openid'];
        $unionId = $session['unionid'] ?? null;
        $user = $this->snsUserDao->findByOpenId($openId, WXAPP);
        if (!empty($user)) {
            return $this->userDao->findById($user->userId);
        }
        if (!empty($unionId)) {
            $user = $this->snsUserDao->findByUnionId($unionId, WXAPP);
            if (!empty($user)) {
                $this->snsUserDao->updateOpenIdByUnionId($unionId, WXAPP, $openId);
                return $this->userDao->findById($user->userId);
            }
        }
        return null;
    }

    public function createUserByWxApp($code, $nickname, $avatarUrl)
    {
        $session = $this->wxAppLogin($code);
        $openId = $session['openid'];
        $unionId = $session['unionid'] ?? null;
        $user = $this->userDao->createUser();
        $this->userDao->update($user->userId, [
            'nickname' => $nickname,
            'avatar_url' => $avatarUrl,
        ]);
        $this->snsUserDao->create([
            'user_id' => $user->userId,
            'type' => WXAPP,
            'open_id' => $openId,
            'union_id' => $unionId,
        ]);
        return $this->userDao->findById($user->userId);
    }

    public function getWebAccessToken($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . WEB_WECHAT_APP_ID . "&secret=" . WEB_WECHAT_APP_SECRET . "&code=$code&grant_type=authorization_code";
        return json_decode(file_get_contents($url), true);
    }

    public function getWebUserInfo($accessToken, $openId)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$accessToken&openid=$openId";
        return json_decode(file_get_contents($url), true);
    }

    public function getUserByWebCode($code)
    {
        $resp = $this->getWebAccessToken($code);
        if (isset($resp['errcode'])) {
            throw new \Exception($resp['errmsg']);
        }
        $accessToken = $resp['access_token'];
        $openId = $resp['openid'];
        $user = $this->snsUserDao->findByOpenId($openId, WECHAT_WEB);
        if (!empty($user)) {
            return $user;
        }
        $userInfo = $this->getWebUserInfo($accessToken, $openId);
        if (isset($userInfo['errcode'])) {
            throw new \Exception($userInfo['errmsg']);
        }
        $unionId = $userInfo['unionid'] ?? null;
        if (!empty($unionId)) {
            $user = $this->snsUserDao->findByUnionId($unionId, WECHAT_WEB);
            if (!empty($user)) {
                $this->snsUserDao->updateOpenIdByUnionId($unionId, WECHAT_WEB, $openId);
                return $user;
            }
        }
        return null;
    }

    public function createUserByWebCode($code, $nickname, $avatarUrl)
    {
        $resp = $this->getWebAccessToken($code);
        $accessToken = $resp['access_token'];
        $openId = $resp['openid'];
        $unionId = $resp['unionid'] ?? null;
        $user = $this->userDao->createUser();
        $this->userDao->update($user->userId, [
            'nickname' => $nickname,
            'avatar_url' => $avatarUrl,
        ]);
        $this->snsUserDao->create([
            'user_id' => $user->userId,
            'type' => WECHAT_WEB,
            'open_id' => $openId,
            'union_id' => $unionId,
        ]);
        return $this->userDao->findById($user->userId);
    }

    public function notifyByWeChat($user, $templateId, $page, $prepayId, array $data)
    {
        $ci = get_instance();
        $ci->load->library('WeChatPlatform');
        $wechat = $ci->wechatplatform;
        $accessToken = $wechat->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$accessToken";
        $postData = [
            'touser' => $user->openId,
            'template_id' => $templateId,
            'page' => $page,
            'form_id' => $prepayId,
            'data' => $data,
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type' => 'application/json']);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}

} // end namespace App\Controllers

// Global namespace: alias so CI3-style global references still work
namespace {
    if (!class_exists('WeChatAppClient', false)) {
        class_alias('App\Controllers\WeChatAppClient', 'WeChatAppClient');
    }
}
