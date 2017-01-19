<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/13/16
 * Time: 8:50 PM
 */
class Pay
{
    /** @var Alipay */
    public $alipay;
    /** @var WxPay */
    public $wepay;

    public $chargeDao;

    function __construct()
    {
        $ci = get_instance();
        $ci->load->library('wx/' . WxPay::class);
        $this->wxpay = new WxPay();
        $ci->load->library('alipay/' . Alipay::class);
        $this->alipay = new Alipay();
        $ci->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
    }

    function createChargeAndInsert($amount, $channel, $subject, $body,
                                   $metaData, $user, $openId)
    {
        $orderNo = genOrderNo();
        $ipAddress = get_instance()->input->ip_address();
        if ($ipAddress == '::1') {
            // local debug case
            $ipAddress = '127.0.0.1';
        }
        $ch = $this->createCharge($orderNo, $channel, $amount, $subject, $body, $openId);
        if ($ch == null) {
            return null;
        }
        $id = $this->chargeDao->add($orderNo, $amount, $channel, $user->userId, $ipAddress, $metaData);
        if (!$id) {
            return null;
        }
        return $ch;
    }


    private function createCharge($orderNo, $channel, $amount, $subject, $body, $openId)
    {
        if ($channel == CHANNEL_ALIPAY_APP) {
            return $this->alipay->createCharge($orderNo, $amount, $subject, $body);
        } else if ($channel == CHANNEL_WECHAT_H5) {
            return $this->wxpay->createCharge($orderNo, $amount, $subject, $body, $openId);
        } else if ($channel == CHANNEL_WECHAT_QRCODE) {
            return $this->wxpay->createQrcodeCharge($orderNo, $amount, $subject, $body);
        } else if ($channel == CHANNEL_WECHAT_APP) {
            return $this->wxpay->createAppCharge($orderNo, $amount, $subject, $body, $openId);
        }
        return null;
    }

    function refund($charge)
    {
        if ($charge->channel == CHANNEL_WECHAT_H5) {
            return $this->wxpay->refund($charge);
        } else {
            logInfo("do not support refund " . json_encode($charge));
        }
        return false;
    }

    function transfer($openId, $name, $amount, $wishing)
    {
        return $this->wxpay->transfer($openId, $name, $amount, $wishing);
    }

    function sendRedPacket($openId, $sendName, $amount, $wishing)
    {
        if (isDebug()) {
            return array(true, null);
        } else {
            return $this->wxpay->sendRedPacket($openId, $sendName, $amount, $wishing);
        }
    }

    function sendGroupRedPacket($openId, $sendName, $amount, $wishing)
    {
        if (isDebug()) {
            return array(true, null);
        } else {
            return $this->wxpay->sendGroupRedPacket($openId, $sendName, $amount, $wishing);
        }
    }
}
