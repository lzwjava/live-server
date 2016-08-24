<?php
/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 8/24/16
 * Time: 9:41 PM
 */

//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$config['partner'] = '2088421737526755';

//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：
//https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
$config['private_key'] = 'MIICXAIBAAKBgQCswBq0v9sTzSuAptPy3++tM5vFDk6hpgyicp/66nPD83vzX4Et
VwO1/tzwiKzaoYqNh2wiCnGsM42usg5VpxCS8FudzCWTiv4WmrTTHWV6v4VHGTM7
x7+2FxxKCv15h/ltF8IQvTR0fUQpeOGSURtGLbcop1G45LG5NRQvsoV3iQIDAQAB
AoGAbu92tvg10cOB2j5IAcI5AjsQHz2O1VPy7Sc7AUEqUn277ZA5PkqQwYEHlRfQ
GR6AAV8j28BlOzZoF/WUKwD8LLH15J259VsgyUjymwxZsGqzaZf90CwKAPCUTF32
AAVEPOpQMI1sWRFYjszSgO1MboraYqNThEbEDTuGIie8Z5kCQQDcWdgyPj3QRSaz
/ftFqEGgia87adk9Q9YGqdXhZd3Vp8ljQRBLVK4waajVNP46aGhssWdn3zlTqV1x
QFlovFwjAkEAyLLQWH+B4j9aGJf9xOcB1pxiO2RiqduZol2SZ4yZNQMSuYWFpeBe
I/ejXRgAf0o7LX9HoWQrcJ8gVrfoKQUyYwJATVyZw+IIT7PHh8/2xRjymYtdY02h
QJotbT95YN4jyRxUXy17rAb59PNTnC8zxovbhbWvKU+8z7uZm+ZMvbHL4wJASuDD
8LCpwcKHF7PlPwg83Ff+IlCCqwK+YCBvFekT4/5ghwz03v1Fwj+LGJly3grQ51mc
XO9lcaA8zX3RxTQmJwJBAJ7wAaMTTIUx4XMWHpwRksVjKyfS3PtNonndPLY1w/Di
zjIuUa4Z/PBrPDK5Xw/7y91WUpIkQnkVzzoSky+ItSw=';

//支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$config['alipay_public_key'] = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCswBq0v9sTzSuAptPy3++tM5vFDk6hpgyicp/66nPD83vzX4EtVwO1/tzwiKzaoYqNh2wiCnGsM42usg5VpxCS8FudzCWTiv4WmrTTHWV6v4VHGTM7x7+2FxxKCv15h/ltF8IQvTR0fUQpeOGSURtGLbcop1G45LG5NRQvsoV3iQIDAQAB';

//异步通知接口
$config['service'] = 'mobile.securitypay.pay';

//签名方式 不需修改
$config['sign_type'] = strtoupper('RSA');

//字符编码格式 目前支持 gbk 或 utf-8
$config['input_charset'] = strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$config['cacert'] = getcwd() . '/cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$config['transport'] = 'http';
