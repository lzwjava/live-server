<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/25/17
 * Time: 1:09 AM
 */
class QiniuLive
{
    private $hub;

    function __construct()
    {
        $credentials = new \Qiniu\Credentials(QINIU_ACCESS_KEY, QINIU_SECRET_KEY);
        $this->hub = new \Pili\Hub($credentials, QINIU_HUB);
    }

    function getPlaybackUrl($live)
    {
        try {
            $stream = $this->hub->getStream('z1.qulive.' . $live->rtmpKey);
            $result = $stream->segments();
            $start = $result['start'];
            $end = $result['end'];
        } catch (Exception $e) {
            logInfo("getStream catch Exception: " . $e->getMessage());
            }
            return null;
        }
    }

}
