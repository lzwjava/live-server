<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/21/17
 * Time: 9:12 PM
 */
class WxAppDao extends BaseDao
{

    /** @var Predis\Client $client */
    public $client;

    function __construct()
    {
        parent::__construct();
        $this->client = $this->newRedisClient(0, 'wxapp:');
    }

    function getAccessToken()
    {
        return $this->client->get('access_token');
    }

    function setAccessToken($token, $ttl)
    {
        $this->client->set('access_token', $token, 'ex', $ttl);
    }

}
