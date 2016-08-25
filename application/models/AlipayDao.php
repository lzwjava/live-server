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
                'notify_url' => 'http://api.hotimg.cn/rewards/notify',
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
            logInfo(json_encode(array("data" => $dataString)));
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

    private function signData1($dataString)
    {
        $alipay_config = $this->config->item('alipay');
        $rsa_sign = urlencode(rsaSign($dataString, $alipay_config['private_key']));
        return $rsa_sign;
    }

    private function makeParamString($array)
    {
        $quotes = array();
        foreach ($array as $key => $value) {
            array_push($quotes, $key . '="' . $value . '"');
        }
        return implode($quotes, '&');
    }

    function sign_post()
    {
        date_default_timezone_set("PRC");
        if ($this->checkIfParamsNotExist($this->post(), array('partner', 'service'))) {
            return;
        }
        $partner = $this->post('partner');
        $service = $this->post('service');
        $alipay_config = $this->config->item('alipay');

        if ($partner != $alipay_config['partner'] || $service != $alipay_config['service']) {
            $this->failure(ERROR_PARTNER_OR_SERVICE);
            return;
        }
        $data = createLinkstring($_POST);

        //将待签名字符串使用私钥签名,且做urlencode. 注意：请求到支付宝只需要做一次urlencode.
        $rsa_sign = urlencode(rsaSign($data, $alipay_config['private_key']));

        //把签名得到的sign和签名类型sign_type拼接在待签名字符串后面。
        $data = $data . '&sign=' . '"' . $rsa_sign . '"' . '&sign_type=' . '"' .
            $alipay_config['sign_type'] .
            '"';

        //返回给客户端,建议在客户端使用私钥对应的公钥做一次验签，保证不是他人传输。
        $this->succeed($data);
    }

    function return_post()
    {
        $alipay_config = $this->config->item('alipay');
        $alipayNotify = new AlipayNotify($alipay_config);
        //注意：在客户端把返回参数请求过来的时候务必要把sign做一次urlencode,保证"+"号字符不会变成空格。
        if ($_POST['success'] == "true")//判断success是否为true.
        {
            //验证参数是否匹配
            if (str_replace('"', '', $_POST['partner']) == $alipay_config['partner'] && str_replace('"', '', $_POST['service']) == $alipay_config['service']) {

                //获取要校验的签名结果
                $sign = $_POST['sign'];

                //除去数组中的空值和签名参数,且把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
                $data = createLinkstring(paraFilter($_POST));

                //logResult('data:'.$data);//调试用，判断待验签参数是否和客户端一致。
                //logResult('sign:'.$sign);//调试用，判断sign值是否和客户端请求时的一致，
                $isSgin = false;

                //获得验签结果
                $isSgin = rsaVerify($data, $alipay_config['alipay_public_key'], $sign);
                if ($isSgin) {
                    echo "return success";
                    //此处可做商家业务逻辑，建议商家以异步通知为准。
                } else {
                    echo "return fail";
                }
            }
        }
    }

    function isSignVerify($params, $sign)
    {
        if (isLocalDebug()) {
            return true;
        }
        return true;
        // todo
        $alipay_config = $this->config->item('alipay');
        $alipayNotify = new AlipayNotify($alipay_config);
        return $alipayNotify->getSignVeryfy($params, $sign);
    }

    function isSignVerify1($params, $sign)
    {
        $publicKey = file_get_contents(APPPATH . 'models/alipay/rsa_public_key.pem');
    }

    function notify_post()
    {
        $alipay_config = $this->config->item('alipay');
        $alipayNotify = new AlipayNotify($alipay_config);
        if ($alipayNotify->getResponse($_POST['notify_id']))//判断成功之后使用getResponse方法判断是否是支付宝发来的异步通知。
        {
            if ($alipayNotify->getSignVeryfy($_POST, $_POST['sign'])) {//使用支付宝公钥验签
                $out_trade_no = $_POST['out_trade_no'];

                //支付宝交易号
                $trade_no = $_POST['trade_no'];

                //交易状态
                $trade_status = $_POST['trade_status'];

                if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                }
                //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
                echo "success";        //请不要修改或删除
            } else //验证签名失败
            {
                echo "sign fail";
            }
        } else //验证是否来自支付宝的通知失败
        {
            echo "response fail";
        }
    }
}
