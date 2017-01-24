<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/25/17
 * Time: 1:09 AM
 */
class Stream
{
    private $hub;

    function __construct()
    {
        $credentials = new \Qiniu\Credentials(QINIU_ACCESS_KEY, QINIU_SECRET_KEY);
        $this->hub = new \Pili\Hub($credentials, QINIU_HUB);
    }

    function getPlaybackUrl($live)
    {
        if ($live->status >= LIVE_STATUS_TRANSCODE) {
            try {
                $stream = $this->hub->getStream('z1.qulive.' . $live->$rtmpKey);
                return $stream['ORIGIN'];
            } catch (Exception $e) {
                logInfo("getStream catch Exception: " . $e->getMessage());
                return null;
            }
        } else {
            return null;
        }

    }

}
