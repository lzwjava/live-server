<?php

namespace App\Libraries;

/**
 * Pay - Payment processing library
 * CI4-compatible version (no get_instance(), no $this->load)
 */
class Pay
{
    public $alipay;
    public $wxpay;
    public $chargeDao;

    public function __construct()
    {
        $this->wxpay = new \App\Libraries\WxPay();
        $this->alipay = new \App\Libraries\Alipay();
        $this->chargeDao = new \App\Models\ChargeDao();
    }

    public function createChargeAndInsert($amount, $channel, $subject, $body,
                                        $metaData, $user, $openId)
    {
        $orderNo = $this->genOrderNo();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($ipAddress === '::1') {
            $ipAddress = '127.0.0.1';
        }
        [$error, $ch, $prepayId] = $this->createCharge($orderNo, $channel, $amount, $subject, $body, $openId);
        if ($error) {
            return [$error, null];
        }
        $id = $this->chargeDao->add($orderNo, $amount, $channel, $user->userId,
            $ipAddress, $metaData, $prepayId);
        if (!$id) {
            return [defined('ERROR_SQL_WRONG') ? ERROR_SQL_WRONG : 'ERROR_SQL_WRONG', null];
        }
        if ($channel == (defined('CHANNEL_WECHAT_QRCODE') ? CHANNEL_WECHAT_QRCODE : 'wechat_qr')) {
            $ch[defined('KEY_ORDER_NO') ? KEY_ORDER_NO : 'orderNo'] = $orderNo;
        }
        return [null, $ch];
    }

    private function createCharge($orderNo, $channel, $amount, $subject, $body, $openId)
    {
        $channelAlipayApp = defined('CHANNEL_ALIPAY_APP') ? CHANNEL_ALIPAY_APP : 'alipay_app';
        $channelWechatH5 = defined('CHANNEL_WECHAT_H5') ? CHANNEL_WECHAT_H5 : 'wechat_h5';
        $channelWechatQr = defined('CHANNEL_WECHAT_QRCODE') ? CHANNEL_WECHAT_QRCODE : 'wechat_qr';
        $channelWechatApp = defined('CHANNEL_WECHAT_APP') ? CHANNEL_WECHAT_APP : 'wechat_app';
        $channelAppleIap = defined('CHANNEL_APPLE_IAP') ? CHANNEL_APPLE_IAP : 'apple_iap';

        if ($channel == $channelAlipayApp) {
            return $this->alipay->createCharge($orderNo, $amount, $subject, $body);
        } elseif ($channel == $channelWechatH5) {
            return $this->wxpay->createCharge($orderNo, $amount, $subject, $body, $openId);
        } elseif ($channel == $channelWechatQr) {
            return $this->wxpay->createQrcodeCharge($orderNo, $amount, $subject, $body);
        } elseif ($channel == $channelWechatApp) {
            return $this->wxpay->createAppCharge($orderNo, $amount, $subject, $body, $openId);
        } elseif ($channel == $channelAppleIap) {
            $keyOrderNo = defined('KEY_ORDER_NO') ? KEY_ORDER_NO : 'orderNo';
            return [null, [$keyOrderNo => $orderNo], null];
        }
        return [defined('ERROR_PARAMETER_ILLEGAL') ? ERROR_PARAMETER_ILLEGAL : 'ERROR_PARAMETER_ILLEGAL', null, null];
    }

    public function refund($charge)
    {
        $channelWechatH5 = defined('CHANNEL_WECHAT_H5') ? CHANNEL_WECHAT_H5 : 'wechat_h5';
        if ($charge->channel == $channelWechatH5) {
            return $this->wxpay->refund($charge);
        }
        if (function_exists('logInfo')) {
            logInfo("do not support refund " . json_encode($charge));
        }
        return false;
    }

    public function transfer($openId, $amount, $desc)
    {
        return $this->wxpay->transfer($openId, $amount, $desc);
    }

    public function sendIncomePacket($openId, $amount)
    {
        return $this->wxpay->sendRedPacket($openId, '趣直播', $amount, '您的努力初见成效，继续加油哦');
    }

    public function sendRedPacket($openId, $sendName, $amount, $wishing)
    {
        if (env('WECHAT_DEBUG') === 'true') {
            return [true, null];
        }
        return $this->wxpay->sendRedPacket($openId, $sendName, $amount, $wishing);
    }

    public function sendGroupRedPacket($openId, $sendName, $amount, $wishing)
    {
        if (env('WECHAT_DEBUG') === 'true') {
            return [true, null];
        }
        return $this->wxpay->sendGroupRedPacket($openId, $sendName, $amount, $wishing);
    }

    private function genOrderNo(): string
    {
        return date('YmdHis') . random_string('numeric', 16);
    }
}
