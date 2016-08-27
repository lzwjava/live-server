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

```
{
    "error": "",
    "result": "partner=\"2088421737526755\"&service=\"mobile.securitypay.pay\"&notify_url=\"http://api.hotimg.cn/rewards/notify\"&_input_charset=\"utf-8\"&it_b_pay=\"30m\"&show_url=\"m.alipay.com\"&total_fee=\"1.00\"&body=\"9705 \u53c2\u52a0 C++ \u7f16\u7a0b\"&out_trade_no=\"c0jDbuuWAr5mwj48\"&seller_id=\"finance@quzhiboapp.com\"&subject=\"9705\u53c2\u52a0\u76f4\u64ad132\"&payment_type=\"1\"&sign_type=\"RSA\"&sign=\"Buk85mD3PR4WpNWE%2BNoS7aU3bAyKb9Zj%2BjH4pWnBFRDg1A9nYlzDiFtkgZwaMPu2%2BmNIY3bJJVrUvUayV7zscU7LV1C2jysxhEOXx8jl1RCPlzgtzu%2FapOZw7hmc2thUPvFGEDPqcn3uUq6u1k2IJqPYs6wBRic%2FTAoPBIbLx4o%3D\"",
    "status": "success"
}
```

result 为支付宝需要的 dataString。 直接调用支付宝 SDK 的 [Alipay payOrder:dataString] 即可。

## lives/:liveId

获取 live 详情。

示例：

curl -X GET http://localhost:3005/lives/158 map[]

response:
```
{
    "error": "",
    "result": {
        "amount": 100,
        "attendanceCount": 0,
        "attendanceId": null,  // 是否报名
        "beginTs": "2016-08-27 16:04:44",
        "canJoin": true,    // 表示是否可以加入直播间，主播和报名者可以进入
        "coverUrl": "http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg",
        "detail": "\u8fd9\u6b21\u4e3b\u8981\u8bb2\u4e0b\u591a\u5e74\u6765 C++ \u7684\u7f16\u7a0b\u5b9e\u6218",
        "endTs": "2016-08-27 16:04:44",
        "liveId": 174,
        "owner": {
            "avatarUrl": "http://obcbndtjd.bkt.clouddn.com/defaultAvatar1.png",
            "userId": 534,
            "username": "53916"
        },
        "ownerId": 534,
        "planTs": "2016-08-27 17:04:44",
        "rtmpKey": "yTOkfcEG",
        "rtmpUrl": "rtmp://quzhiboapp.com/live/yTOkfcEG",
        "status": 2,
        "subject": "C++ \u7f16\u7a0b"
    },
    "status": "success"
}
```


## lives/attended

我报名过的直播

curl -X GET http://localhost:3005/lives/attended map[]

response: 

```
{
    "error": "",
    "result": [
        {
            "amount": 100,
            "attendanceCount": 1,
            "attendanceId": 43,
            "beginTs": "2016-08-27 16:34:30",
            "canJoin": true,
            "coverUrl": "http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg",
            "detail": "\u8fd9\u6b21\u4e3b\u8981\u8bb2\u4e0b\u591a\u5e74\u6765 C++ \u7684\u7f16\u7a0b\u5b9e\u6218",
            "endTs": "2016-08-27 16:34:30",
            "liveId": 181,
            "owner": {
                "avatarUrl": "http://obcbndtjd.bkt.clouddn.com/defaultAvatar1.png",
                "userId": 546,
                "username": "88090"
            },
            "ownerId": 546,
            "planTs": "2016-08-27 17:34:30",
            "rtmpKey": "TIiUM1fz",
            "rtmpUrl": "rtmp://quzhiboapp.com/live/TIiUM1fz",
            "status": 2,
            "subject": "C++ \u7f16\u7a0b"
        }
    ],
    "status": "success"
}
```

## lives/me

我发起的直播

curl -X GET http://localhost:3005/lives/me map[]



