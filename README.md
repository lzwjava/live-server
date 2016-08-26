# live-server

Deploy: fab -H root@test.reviewcode.cn deploy

Install dependencies: composer install, composer update


# API

## users/isRegister

根据手机号码判断是否已注册

示例:

curl -X GET http://localhost:3005/users/isRegister map[mobilePhoneNumber:[13274100361]]

response: {"status":"success","result":false,"error":""}

## qrcodes

上传扫描的二维码编号，绑定用户。

示例：

curl -X POST http://localhost:3005/qrcodes map[code:[quzhibo-YFn0NE6UFXUQJonbNVdWTxti1wDpUo3q]]

response: {"status":"success","result":{},"error":""}

## attendances

报名直播。

示例：

curl -X POST http://localhost:3005/attendances map[liveId:[132]]

response: 

{"status":"success","result":"partner=\"2088421737526755\"&service=\"mobile.securitypay.pay\"&notify_url=\"http:\/\/api.hotimg.cn\/rewards\/notify\"&_input_charset=\"utf-8\"&it_b_pay=\"30m\"&show_url=\"m.alipay.com\"&total_fee=\"1.00\"&body=\"9705 \u53c2\u52a0 C++ \u7f16\u7a0b\"&out_trade_no=\"c0jDbuuWAr5mwj48\"&seller_id=\"finance@quzhiboapp.com\"&subject=\"9705\u53c2\u52a0\u76f4\u64ad132\"&payment_type=\"1\"&sign_type=\"RSA\"&sign=\"Buk85mD3PR4WpNWE%2BNoS7aU3bAyKb9Zj%2BjH4pWnBFRDg1A9nYlzDiFtkgZwaMPu2%2BmNIY3bJJVrUvUayV7zscU7LV1C2jysxhEOXx8jl1RCPlzgtzu%2FapOZw7hmc2thUPvFGEDPqcn3uUq6u1k2IJqPYs6wBRic%2FTAoPBIbLx4o%3D\"","error":""}

result 为支付宝需要的 dataString。 直接调用支付宝 SDK 的 [Alipay payOrder:dataString] 即可。

## attedances/one

获取报名记录

curl -X GET http://localhost:3005/attendances/one map[liveId:[133]]

response: {"status":"success","result":{"attendanceId":25,"userId":468,"liveId":133,"orderNo":"qnJY0RtQOWBeZkrZ","created":"2016-08-26 19:44:08"},"error":""}

