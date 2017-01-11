<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/14/16
 * Time: 1:09 AM
 */
class WxDao extends BaseDao
{

    /** @var Predis\Client $client */
    public $client;

    function __construct()
    {
        parent::__construct();
        $this->client = $this->newRedisClient(0, 'wx:');
    }

    function getAccessToken()
    {
        return $this->client->get('access_token');
    }

    function setAccessToken($token, $ttl)
    {
        $this->client->set('access_token', $token, 'ex', $ttl);
    }

    function getJSApiTicket()
    {
        return $this->client->get('jsapi_ticket');
    }

    function setJSApiTicket($token, $ttl)
    {
        $this->client->set('jsapi_ticket', $token, 'ex', $ttl);
    }

    function setThirdSession($thirdSession, $data)
    {
        $ttl = 60 * 60 * 24 * 7;
        return $this->client->set($thirdSession, json_encode($data), 'ex', $ttl);
    }

    function getThirdSession($thirdSession)
    {
        $thirdStr = $this->client->get($thirdSession);
        if (!$thirdStr) {
            return null;
        }
        return json_decode($thirdStr);
    }

}
