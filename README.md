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

