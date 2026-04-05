<?php

namespace App\Libraries;

/**
 * Alipay stub for CI4 — not a real payment implementation.
 * Returns safe error responses so controllers don't crash.
 * Replace with real Alipay SDK integration when ready.
 */
class Alipay
{
    public function __construct()
    {
        // Load alipay config if available
        $config = config('Alipay');
        if ($config) {
            $this->config = $config;
        }
    }

    private function alipayConfig()
    {
        if (isset($this->config)) {
            return (array) $this->config;
        }
        // Fallback to env vars
        return [
            'partner' => env('ALIPAY_PARTNER', ''),
            'service' => env('ALIPAY_SERVICE', 'mobile.securitypay.pay'),
            'notify_url' => env('ALIPAY_NOTIFY_URL', ''),
        ];
    }

    /**
     * Create an Alipay charge.
     * @return array [error, paymentString, prepayId]
     */
    public function createCharge($orderNo, $amount, $subject, $body)
    {
        // Stub: return an error indicating payment is not configured
        return array('Alipay not configured — stub implementation', null, null);
    }

    /**
     * Verify Alipay callback signature.
     * @return bool
     */
    public function isSignVerify($params, $sign)
    {
        if (isDebug()) {
            return true;
        }
        log_message('error', 'Alipay::isSignVerify called — stub implementation');
        return false;
    }
}
