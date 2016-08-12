# live-server

Deploy: fab -H root@test.reviewcode.cn deploy

Install dependencies: composer install, composer update


# API

## users/isRegister

根据手机号码判断是否已注册

示例:

curl -X GET http://localhost:3005/users/isRegister map[mobilePhoneNumber:[13274100361]]
response: {"status":"success","result":false,"error":""}

