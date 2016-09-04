<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 8/24/16
 * Time: 7:21 PM
 */
require_once("alipay/alipay_notify.class.php");
require_once("alipay/alipay_rsa.function.php");
require_once("alipay/alipay_core.function.php");

class AlipayDao extends BaseDao
{
    function __construct()
    {
        parent::__construct();
        $this->config->load('alipay', TRUE);
    }

    function createCharge($orderNo, $channel, $amount, $subject, $body)
    {
        if ($channel == 'alipay') {
            $alipay_config = $this->config->item('alipay');
            $partner = $alipay_config['partner'];
            $service = $alipay_config['service'];
            $fee = sprintf('%.2f', $amount / 100.0);
            $order = array(
                'partner' => $partner,
                'service' => $service,
                'notify_url' => ALIPAY_NOTIFY_URL,
                '_input_charset' => 'utf-8',
                'it_b_pay' => '30m',
                'show_url' => 'm.alipay.com',
                'total_fee' => $fee,
                'body' => $body,
                'out_trade_no' => $orderNo,
                'seller_id' => 'finance@quzhiboapp.com',
                'subject' => $subject,
                'payment_type' => '1'
            );
            $dataString = $this->makeParamString($order);
            $sign = $this->signData($dataString);
            $dataString .= '&sign_type="RSA"&sign="' . $sign . '"';
            // logInfo(json_encode(array("data" => $dataString)));
            return $dataString;
        } else if ($channel == 'weiwin') {

        }
    }

    private function signData($dataString)
    {
        $privateKey = file_get_contents(APPPATH . 'models/alipay/rsa_private_key.pem');
        $res = openssl_get_privatekey($privateKey);
        openssl_sign($dataString, $sign, $res);
        openssl_free_key($res);
        $sign = urlencode(base64_encode($sign));
        return $sign;
    }

    private function makeParamString($array)
    {
        $quotes = array();
        foreach ($array as $key => $value) {
            array_push($quotes, $key . '="' . $value . '"');
        }
        return implode($quotes, '&');
    }

    function isSignVerify($params, $sign)
    {
        if (isDebug()) {
            return true;
        }
        return true;
        // todo
        $alipay_config = $this->config->item('alipay');
        $alipayNotify = new AlipayNotify($alipay_config);
        return $alipayNotify->getSignVeryfy($params, $sign);
    }

}
