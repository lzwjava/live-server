<?php
header("Content-type: text/html; charset=utf-8");
include_once 'Config.php';
include_once 'Request/V20170525/SendSmsRequest.php';
include_once 'Request/V20170525/QuerySendDetailsRequest.php';

function sendSms($phoneNumber, $code)
{

    //此处需要替换成自己的AK信息
    $accessKeyId = ALIYUN_KEY_ID;
    $accessKeySecret = ALIYUN_KEY_SECRET;
    //短信API产品名
    $product = "Dysmsapi";
    //短信API产品域名
    $domain = "dysmsapi.aliyuncs.com";
    //暂时不支持多Region
    $region = "cn-hangzhou";

    //初始化访问的acsCleint
    $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
    DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
    $acsClient = new DefaultAcsClient($profile);

    $request = new Dysmsapi\Request\V20170525\SendSmsRequest;
    //必填-短信接收号码
    $request->setPhoneNumbers($phoneNumber);
    //必填-短信签名
    $request->setSignName(ALIYUN_SIGN_NAME);
    //必填-短信模板Code
    $request->setTemplateCode(ALIYUN_TEMPLATE_CODE);
    //选填-假如模板中存在变量需要替换则为必填(JSON格式)
    $request->setTemplateParam(json_encode([  // 短信模板中字段的值
        'code' => $code
    ]));
    //选填-发送短信流水号
    $request->setOutId(random_string('alnum', 8));

    //发起访问请求
    $acsResponse = $acsClient->getAcsResponse($request);

    return $acsResponse;
}

function querySendDetails($phoneNumber)
{

    //此处需要替换成自己的AK信息
    $accessKeyId = ALIYUN_KEY_ID;
    $accessKeySecret = ALIYUN_KEY_SECRET;
    //短信API产品名
    $product = "Dysmsapi";
    //短信API产品域名
    $domain = "dysmsapi.aliyuncs.com";
    //暂时不支持多Region
    $region = "cn-hangzhou";

    //初始化访问的acsCleint
    $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
    DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
    $acsClient = new DefaultAcsClient($profile);

    $request = new Dysmsapi\Request\V20170525\QuerySendDetailsRequest();
    //必填-短信接收号码
    $request->setPhoneNumber($phoneNumber);
    //选填-短信发送流水号
    $request->setBizId("abcdefgh");
    //必填-短信发送日期，支持近30天记录查询，格式yyyyMMdd
    $request->setSendDate("20180327");
    //必填-分页大小
    $request->setPageSize(10);
    //必填-当前页码
    $request->setContent(1);

    //发起访问请求
    $acsResponse = $acsClient->getAcsResponse($request);
//    var_dump($acsResponse);

}

//sendSms();
//querySendDetails();
?>