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
        $this->client->set('access_token', $token, 'ex', 300);
    }

    function getJSApiTicket()
    {
        return $this->client->get('jsapi_ticket');
    }

    function setJSApiTicket($token, $ttl)
    {
        $this->client->set('jsapi_ticket', $token, 'ex', 300);
    }

}
