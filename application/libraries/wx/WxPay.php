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
            $tools = new JsApiPay();
            $input->SetOpenid($openId);
            $order = WxPayApi::unifiedOrder($input, 15);
            $jsApiParameters = $tools->GetJsApiParameters($order);
            return $jsApiParameters;
        } else {
            $input->SetProduct_id($orderNo);
            $order = WxPayApi::unifiedOrder($input, 15);
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

    function refund($charge)
    {
        $out_trade_no = $charge->orderNo;
        $total_fee = $charge->amount;
        $refund_fee = $charge->amount;
        $input = new WxPayRefund();
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no(WxPayConfig::MCHID . date("YmdHis"));
        $input->SetOp_user_id(WxPayConfig::MCHID);
        $refundResult = WxPayApi::refund($input, 30);
        logInfo("refundResult: " . json_encode($refundResult));
        if ($refundResult['return_code'] == 'SUCCESS') {
            return true;
        } else {
            logInfo("refund failed!!!");
            return false;
        }
    }

    function transfer()
    {
        $input = new WxPayTransferItem();
        $orderNo = genOrderNo();
        $input->SetPartnerTradeNo($orderNo);
        $input->SetAmount(100);
        $input->SetOpenid('ol0AFwFe5jFoXcQby4J7AWJaWXIM');
        $input->SetDesc('测试');
        $transferResult = WxPayApi::transfer($input);
        logInfo("transferResult: " . json_encode($transferResult));
        if ($transferResult['result_code'] == 'SUCCESS') {
            return true;
        } else {
            logInfo("refund failed!!!");
            return false;
        }
    }

    function sendRedPacket($openId, $sendName, $amount, $wishing)
    {
        $input = new WxPayRedPacket();
        $orderNo = genOrderNo();
        $input->SetMchBillNo($orderNo);
        $input->SetTotalAmount($amount);
        $input->SetTotalNum(1);
        $input->SetOpenid($openId);
        $input->SetRemark('新年快乐');
        $input->SetSendName($sendName);
        $input->SetWishing($wishing);
        $input->SetActName('新年快乐红包');
        $input->SetRemark('新年快乐');
        $transferResult = WxPayApi::sendRedPacket($input);
        logInfo("transferResult: " . json_encode($transferResult));
        if ($transferResult['result_code'] == 'SUCCESS') {
            return true;
        } else {
            logInfo("send red packet failed!!!");
            return false;
        }
    }

}
