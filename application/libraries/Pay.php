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

    function __construct()
    {
        $ci = get_instance();
        $ci->load->library('wx/' . WxPay::class);
        $this->wxpay = new WxPay();
        $ci->load->library('alipay/' . Alipay::class);
        $this->alipay = new Alipay();
    }

    function createCharge($orderNo, $channel, $amount, $subject, $body, $openId)
    {
        if ($channel == CHANNEL_ALIPAY_APP) {
            return $this->alipay->createCharge($orderNo, $channel, $amount, $subject, $body);
        } else if ($channel == CHANNEL_WECHAT_H5) {
            return $this->wxpay->createCharge($orderNo, $channel, $amount, $subject, $body, $openId);
        }
        return null;
    }
}
