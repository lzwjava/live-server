<?php

namespace App\Libraries;

/**
 * QiniuLive - Qiniu Pili live streaming integration
 * CI4-compatible version
 */
class QiniuLive
{
    private $hub = null;

    public function __construct()
    {
        if (env('WECHAT_DEBUG') === 'true') {
            return;
        }
        if (!class_exists('\Qiniu\Credentials')) {
            if (function_exists('logInfo')) {
                logInfo("Qiniu\\Credentials class not found - qiniu/php-sdk may not be installed");
            }
            return;
        }
        try {
            $credentials = new \Qiniu\Credentials(env('QINIU_ACCESS_KEY'), env('QINIU_SECRET_KEY'));
            $this->hub = new \Pili\Hub($credentials, env('QINIU_HUB'));
        } catch (\Throwable $e) {
            if (function_exists('logInfo')) {
                logInfo("QiniuLive init error: " . $e->getMessage());
            }
        }
    }

    public function getPlaybackUrl($live)
    {
        if (env('WECHAT_DEBUG') === 'true' || $this->hub === null) {
            return null;
        }
        try {
            $stream = $this->hub->getStream('z1.qulive.' . $live->rtmpKey);
            $result = $stream->segments();
            $segment0 = $result['segments'][0];
            $start = $segment0['start'];
            $end = $segment0['end'];
            $resp = $stream->saveAs('playback.m3u8', 'm3u8', $start, $end);
            return $resp['url'];
        } catch (\Exception $e) {
            if (function_exists('logInfo')) {
                logInfo("getStream catch Exception: " . $e->getMessage());
            }
            if (function_exists('isDebug') && isDebug()) {
                return true;
            }
            return null;
        }
    }
}
