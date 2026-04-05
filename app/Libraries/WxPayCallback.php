<?php

namespace App\Libraries;

use App\Models\PayNotifyDao;
use Psr\Log\LoggerInterface;

/**
 * WxPayCallback stub for CI4.
 * Handles WeChat payment callback notifications.
 * Replace with real WxPay SDK integration when ready.
 */
class WxPayCallback
{
    public $payNotifyDao;

    public function __construct()
    {
        date_default_timezone_set('Asia/Shanghai');
        $this->payNotifyDao = new PayNotifyDao();
    }

    /**
     * Query order status by transaction ID.
     * @return bool
     */
    public function Queryorder($transaction_id)
    {
        log_message('error', 'WxPayCallback::Queryorder called — stub implementation');
        return false;
    }

    /**
     * Process the notification data.
     * @param array $data  Post data from WeChat
     * @param string $msg  Error message output
     * @return bool
     */
    public function NotifyProcess($data, &$msg)
    {
        if (!array_key_exists('transaction_id', $data)) {
            $msg = '输入参数不正确';
            return false;
        }
        log_message('info', 'wechat notify data: ' . json_encode($data));
        $outTradeNo = $data['out_trade_no'];
        $error = $this->payNotifyDao->handleChargeSucceed($outTradeNo);
        if ($error) {
            log_message('error', 'wechat charge notify failed: ' . $error);
            $msg = $error;
            return false;
        }
        return true;
    }

    /**
     * Entry point called by Wechat::wxpayNotify().
     * In a real implementation this would call WxPayNotify::Handle().
     * Stub always echoes "success" to avoid WeChat retry storms.
     */
    public function Handle($needSign = false)
    {
        $xmlStr = file_get_contents('php://input');
        if (empty($xmlStr)) {
            echo 'success';
            return;
        }

        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        $msg = '';
        $result = $this->NotifyProcess($data, $msg);

        if ($result) {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>';
        } else {
            echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[' . $msg . ']]></return_msg></xml>';
        }
    }
}
