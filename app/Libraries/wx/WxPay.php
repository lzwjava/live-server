<?php

namespace App\Libraries;

/**
 * WxPay stub for CI4 — not a real payment implementation.
 * Returns safe error responses so controllers don't crash.
 * Replace with real WxPay SDK integration when ready.
 */
class WxPay
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    /**
     * Create a JSAPI payment charge.
     * @return array [error, jsApiParameters, prepayId]
     */
    public function createCharge($orderNo, $amount, $subject, $body, $openId)
    {
        return $this->baseCreateCharge($orderNo, $amount, $subject, $body, $openId, 'JSAPI');
    }

    /**
     * Create an app payment charge.
     */
    public function createAppCharge($orderNo, $amount, $subject, $body, $openId)
    {
        return $this->baseCreateCharge($orderNo, $amount, $subject, $body, $openId, 'JSAPI');
    }

    /**
     * Create a native QR code payment charge.
     */
    public function createQrcodeCharge($orderNo, $amount, $subject, $body)
    {
        return $this->baseCreateCharge($orderNo, $amount, $subject, $body, null, 'NATIVE');
    }

    private function baseCreateCharge($orderNo, $amount, $subject, $body, $openId, $tradeType)
    {
        // Stub: return an error indicating payment is not configured
        return array('WxPay not configured — stub implementation', null, null);
    }

    /**
     * Process a refund.
     * @return bool
     */
    public function refund($charge)
    {
        log_message('error', 'WxPay::refund called — stub implementation');
        return false;
    }

    /**
     * Transfer money to a user.
     * @return array [success, errorMessage]
     */
    public function transfer($openId, $amount, $desc)
    {
        log_message('error', 'WxPay::transfer called — stub implementation');
        return array(false, 'WxPay stub: transfer not implemented');
    }

    /**
     * Send a single red packet.
     * @return array [success, errorMessage]
     */
    public function sendRedPacket($openId, $sendName, $amount, $wishing)
    {
        log_message('error', 'WxPay::sendRedPacket called — stub implementation');
        return array(false, 'WxPay stub: sendRedPacket not implemented');
    }

    /**
     * Send a group red packet.
     * @return array [success, errorMessage]
     */
    public function sendGroupRedPacket($openId, $sendName, $amount, $wishing)
    {
        log_message('error', 'WxPay::sendGroupRedPacket called — stub implementation');
        return array(false, 'WxPay stub: sendGroupRedPacket not implemented');
    }
}
