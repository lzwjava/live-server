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
//            $start = $result['start'];
//            $end = $result['end'];

//
            $segment0 = $result['segments'][0];
            $start = $segment0['start'];
            $end = $segment0['end'];

            $resp = $stream->saveAs('playback.m3u8', 'm3u8', $start, $end);
            $playbackUrl = $resp['url'];
            return $playbackUrl;
        } catch (Exception $e) {
            logInfo("getStream catch Exception: " . $e->getMessage());
            if (isDebug()) {
                return true;
            }
            return null;
        }
    }

}
