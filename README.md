# live-server

Deploy: fab -H root@test.reviewcode.cn deploy

Install dependencies: composer install, composer update


# Model

## live

```
{
    "amount": 100,           // 门票，当 needPay = 0 时无作用
    "attendanceCount": 1,    // 当前参与的人数
    "attendanceId": 880,     // 报名对应的 ID
    "beginTs": "2017-01-16 16:23:18",   
    "canJoin": true,         // 是否能够进入房间，报名之后为 true
    "conversationId": "587c82f661ff4b006b5ef3a0", // 聊天用的对话 ID
    "coverUrl": "http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg", // 封面图
    "created": "2017-01-16 16:23:18",
    "detail": "\u6211\u662f\u5468\u5b50\u656c\uff0c\u4ee5\u592a\u8d44\u672c\u521b\u59cb\u4eba\u517c",                             // 主播详情
    "endTs": "2017-01-16 16:23:18", 
    "flvUrl": "http://pili-live-hls.quzhiboapp.com/qulive/QcmSl5MK.flv",  // 桌面用的播放地址
    "hlsUrl": "http://pili-live-hls.quzhiboapp.com/qulive/QcmSl5MK.m3u8",  // 微信网页用的播放地址
    "liveId": 2454,     // 直播 ID
    "maxPeople": 2000,  // 最多报名人数，客户端用不上
    "needPay": 0,       // 是否需要付费
    "notice": "",       // 房间公告
    "owner": {          // 主播信息
        "avatarUrl": "http://i.quzhiboapp.com/defaultAvatar1.png",
        "userId": 5071,
        "username": "36702744"
    },
    "ownerId": 5071,     
    "planTs": "2017-01-16 17:23:18",  // 计划的直播时间
    "previewUrl": "",                
    "realAmount": 100,                // 实际的门票价格，可能由于用户分享而较低
    "rtmpKey": "QcmSl5MK",            
    "rtmpUrl": "rtmp://xycdn.quzhiboapp.com/live/QcmSl5MK",  // iOS 的直播地址
    "shareIcon": 0,                   // 0 为分享的时候显示主播头像，1 为显示直播封面
    "shareId": null,
    "speakerIntro": "\u6211\u662f\u609f\u7a7a\uff0c\u70ed\u7231\u65c5\u884c\uff0c\u5c0f",                              // 主播简介
    "status": 10,
    "subject": "C++ \u7f16\u7a0b",    // 直播标题
    "updated": "2017-01-16 16:23:18",  
    "videoUrl": "http://video-qncdn.quzhiboapp.com/QcmSl5MK.mp4",  // 过时字段，不再用
    "pushUrl": "rtmp://cheer.quzhiboapp.com/live/cknfu6za"  // 推流地址
}
```

在没有报名的时候，将没有 rtmpKey、rtmpUrl、hlsUrl、flvUrl 等字段, canJoin 为 false ，报名之后才有这些字段。

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

用 live.needPay 来判断是否需要付费报名。

1）付费报名直播。

示例：

curl -X POST http://localhost:3005/attendances map[liveId:[2452] channel:[alipay_app]]

response: 

```
{
    "error": "",
    "result": "partner=\"2088421737526755\"&service=\"mobile.securitypay.pay\"&notify_url=\"http://api.quzhiboapp.com/rewards/notify\"&_input_charset=\"utf-8\"&it_b_pay=\"30m\"&show_url=\"m.alipay.com\"&total_fee=\"1.00\"&body=\"855919044 \u53c2\u52a0\u76f4\u64ad C++ \u7f16\u7a0b\"&out_trade_no=\"OrVUbkuyR7rab1Fu\"&seller_id=\"finance@quzhiboapp.com\"&subject=\"\u53c2\u52a0\u76f4\u64ad\"&payment_type=\"1\"&sign_type=\"RSA\"&sign=\"RmXFHW224ehmvAMBnVtfq6tCxAyiS8MhG9yy63DG8MMiI%2BiD7gfXddDQSz%2Ff7oXx6vaSynlldD85YETCiTN6HyEj51PfYiefPQKFjuT4OL%2BJ4iQYdn3BcUUs8BrSWT8b6dx5TNZpGpexmXo4AP3GmBH%2BSgJuy%2Foqj81Q%2FZUzrVg%3D\"",
    "status": "success"
}
```

result 为支付宝需要的 dataString。 直接调用支付宝 SDK 的 [Alipay payOrder:dataString] 即可。

2）免费报名直播

curl -X POST http://localhost:3005/attendances map[liveId:[2454]]

response:

```
{"status":"success","result":{},"error":""}
```


## lives/:liveId

获取 live 详情。

示例：

curl -X GET http://localhost:3005/lives/158 map[]

response:
```
{
    "error": "",
    "result": {
        "amount": 0,
        "attendanceCount": 0,
        "attendanceId": null, // 是否报名
        "beginTs": "2016-09-04 18:33:12",
        "canJoin": false, //表示是否可以加入直播间，主播和报名者可以进入
        "conversationId": "57cbf868a22b9d006b9e6cf2", // 聊天室 ID
        "coverUrl": "",
        "detail": "",
        "endTs": "2016-09-04 18:33:12",
        "liveId": 244,
        "maxPeople": 300,
        "owner": {
            "avatarUrl": "http://obcbndtjd.bkt.clouddn.com/defaultAvatar1.png",
            "userId": 637,
            "username": "606473635"
        },
        "ownerId": 637,
        "planTs": "2016-09-04 18:33:12",
        "status": 1,
        "subject": "606473635\u7684\u76f4\u64ad"
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


## wechat/bind

绑定微信

curl -X GET http://localhost:3005/wechat/bind map[code:[021mZiQa0Ralxu1L7kNa0RgeQa0mZiQP]]

绑定成功，user 字段中会带有一个 `unionId`。


