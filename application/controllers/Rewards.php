<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午8:32
 */
class Rewards extends BaseController
{
    public $alipay;
    public $payNotifyDao;

    function __construct()
    {
        parent::__construct();
        $this->load->library('alipay/' . Alipay::class);
        $this->alipay = new Alipay();
        $this->load->model(PayNotifyDao::class);
        $this->payNotifyDao = new PayNotifyDao();
    }

    public function notify_post()
    {
        $content = file_get_contents("php://input");
        logInfo("notify $content");
        if ($this->alipay->isSignVerify($_POST, $_POST['sign'])) {
            $outTradeNo = $_POST['out_trade_no'];
            $trade_status = $_POST['trade_status'];
            if ($trade_status == 'TRADE_SUCCESS') {
                $error = $this->payNotifyDao->handleChargeSucceed($outTradeNo, CHANNEL_ALIPAY_APP);
                if ($error) {
                    logInfo("error: " . $error);
                    $this->failure($error);
                    return;
                }
            }
            echo 'success';
        } else {
            logInfo("sign failed");
            $this->failure(ERROR_SIGN_FAILED);
        }
    }

    function success_get()
    {
        $params = $this->get();
        $paramsStr = json_encode($params);
        logInfo("reward success $paramsStr");
    }

}
