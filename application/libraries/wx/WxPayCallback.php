<?php

require_once "lib/WxPay.Api.php";
require_once 'lib/WxPay.Notify.php';

class WxPayCallback extends WxPayNotify
{

    public $payNotifyDao;

    function __construct()
    {
        date_default_timezone_set('Asia/Shanghai');

        $ci = get_instance();
        $ci->load->model(PayNotifyDao::class);
        $this->payNotifyDao = new PayNotifyDao();
    }

    //查询订单
    public function Queryorder($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS"
        ) {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            return false;
        }
        $outTradeNo = $data['out_trade_no'];
        $error = $this->payNotifyDao->handleChargeSucceed($outTradeNo);
        if ($error) {
            logInfo("wechat charge notify failed: " . $error);
            return false;
        }
        return true;
    }
}

