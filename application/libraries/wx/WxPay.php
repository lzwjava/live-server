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

    private function baseCreateCharge($orderNo, $amount, $subject, $body, $openId, $tradeType)
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
        $input->SetTrade_type($tradeType);
        if ($tradeType == "JSAPI") {
            $input->SetOpenid($openId);
            $order = WxPayApi::unifiedOrder($input);
            $jsApiParameters = $tools->GetJsApiParameters($order);
            return $jsApiParameters;
        } else {
            $input->SetProduct_id($orderNo);
            $order = WxPayApi::unifiedOrder($input);
            // logInfo("order: " . json_encode($order));
            return array("code_url" => $order["code_url"]);
        }
    }

    function createCharge($orderNo, $amount, $subject, $body, $openId)
    {
        return $this->baseCreateCharge($orderNo, $amount, $subject, $body, $openId, "JSAPI");
    }

    function createQrcodeCharge($orderNo, $amount, $subject, $body)
    {
        return $this->baseCreateCharge($orderNo, $amount, $subject, $body, null, "NATIVE");
    }

}
