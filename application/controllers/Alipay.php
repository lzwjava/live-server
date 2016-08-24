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

class Alipay extends BaseController
{
    function __construct()
    {
        parent::__construct();
        $this->config->load('alipay', TRUE);
    }

    function sign_post()
    {
        date_default_timezone_set("PRC");
        if ($this->checkIfParamsNotExist($this->post(), array('partner'))) {
            return;
        }
        $partner = $this->post('partner');
        $alipay_config = $this->config->item('alipay');

        $service = $alipay_config['service'];

        if ($partner != $alipay_config['partner']) {
            $this->failure(ERROR_PARTNER);
            return;
        }
        $data = createLinkstring($_POST);

        //将待签名字符串使用私钥签名,且做urlencode. 注意：请求到支付宝只需要做一次urlencode.
        $rsa_sign = urlencode(rsaSign($data, $alipay_config['private_key']));

        //把签名得到的sign和签名类型sign_type拼接在待签名字符串后面。
        $data = $data . '&service=' . $service . '&sign=' . '"' . $rsa_sign . '"' . '&sign_type=' . '"' .
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

    function notify_post()
    {
        $alipay_config = $this->config->item('alipay');
        $alipayNotify = new AlipayNotify($alipay_config);
        if ($alipayNotify->getResponse($_POST['notify_id']))//判断成功之后使用getResponse方法判断是否是支付宝发来的异步通知。
        {
            if ($alipayNotify->getSignVeryfy($_POST, $_POST['sign'])) {//使用支付宝公钥验签

                //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
                //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
                //商户订单号
                $out_trade_no = $_POST['out_trade_no'];

                //支付宝交易号
                $trade_no = $_POST['trade_no'];

                //交易状态
                $trade_status = $_POST['trade_status'];

                if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                    //判断该笔订单是否在商户网站中已经做过处理
                    //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                    //如果有做过处理，不执行商户的业务程序
                    //注意：
                    //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
                    //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                    //判断该笔订单是否在商户网站中已经做过处理
                    //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                    //如果有做过处理，不执行商户的业务程序
                    //注意：
                    //付款完成后，支付宝系统发送该交易状态通知
                    //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
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