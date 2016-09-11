<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/11/16
 * Time: 5:22 PM
 */

require_once "lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';

class WxPay
{
    private $log;
    private $logHandler;

    function __construct()
    {
        date_default_timezone_set('Asia/Shanghai');
        $this->logHandler = new CLogFileHandler("../logs/" . date('Y-m-d') . '.log');
        $this->log = Log::Init($this->logHandler, 15);
    }

    function createWxOrder()
    {
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid();
        $input = new WxPayUnifiedOrder();
        $input->SetBody("test");
        $input->SetAttach("test");
        $input->SetOut_trade_no(WxPayConfig::MCHID . date("YmdHis"));
        $input->SetTotal_fee("1");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        return $jsApiParameters;
    }

}