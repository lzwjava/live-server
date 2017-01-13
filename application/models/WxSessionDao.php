<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/11/17
 * Time: 9:08 PM
 */
class WxSessionDao extends BaseDao
{
    /** @var Predis\Client $client */
    public $client;

    function __construct()
    {
        parent::__construct();
        $this->client = $this->newRedisClient(2, 'session:');
    }

    function setOpenIdAndSessionKey($thirdSession, $data)
    {
        $ttl = $data->expires_in;
        unset($data->expires_in);
        $ttl = 60 * 10;
        return $this->client->set($thirdSession, json_encode($data), 'ex', $ttl);
    }

    function getOpenIdAndSessionKey($thirdSession)
    {
        $thirdStr = $this->client->get($thirdSession);
        if (!$thirdStr) {
            return null;
        }
        return json_decode($thirdStr);
    }
}