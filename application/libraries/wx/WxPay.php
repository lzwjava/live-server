<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/11/16
 * Time: 5:22 PM
 */

require_once "lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";

class WxPay
{
    function __construct()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    function createCharge($orderNo, $channel, $amount, $subject, $body, $openId)
    {
        $tools = new JsApiPay();
        $input = new WxPayUnifiedOrder();
        $input->SetBody($subject);
        $input->SetDetail($body);
        $input->SetAttach('');
        $input->SetOut_trade_no($orderNo);
        $input->SetTotal_fee($amount);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag('');
        $input->SetNotify_url("http://api.quzhiboapp.com/wechat/wxpayNotify");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        return $jsApiParameters;
    }

}
