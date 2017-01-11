package liveserver

import (
	"fmt"
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestWeChat_sign(t *testing.T) {
	c := NewClient()
	res := c.getData("wechat/sign", url.Values{"url": {"http://localhost:9060/?code=04142OU20noPOy1tgZW20aLUU2042OUk&state=123"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("appId").Interface())
	assert.NotNil(t, res.Get("nonceStr").Interface())
}

func TestWeChat_oauth(t *testing.T) {
	c := NewClient()
	deleteSnsUser()
	res := c.get("wechat/oauth", url.Values{"code": {"031AdaVs0LDcYb13AQWs07EjVs0AdaV7"}})
	assert.NotNil(t, res)
}

func TestWeChat_oauth_then_register(t *testing.T) {
	c, _ := NewClientAndUser()
	deleteSnsUser()
	res := c.get("wechat/oauth", url.Values{"code": {"041TMsWH0vWcWj2tFIYH0E2tWH0TMsWa"}})
	assert.NotNil(t, res)
	if res.Get("status").MustString() == "success" {
		result := res.Get("result")
		res := c.postData("users/registerBySns", url.Values{"openId": {result.Get("openId").MustString()},
			"platform": {"wechat"}})
		assert.NotNil(t, res)
	}
}

func TestWeChat_oauth_then_registerWithMobile(t *testing.T) {
	c, _ := NewClientAndUser()
	deleteSnsUser()
	res := c.get("wechat/oauth", url.Values{"code": {"031Czzd81oYlsR1ZE3d81MSFd81CzzdN"}})
	assert.NotNil(t, res)
	if res.Get("status").MustString() == "success" {
		result := res.Get("result")
		res := c.postData("users/registerBySns", url.Values{"openId": {result.Get("openId").MustString()},
			"platform": {"wechat"}, "mobilePhoneNumber": {randomMobile()}, "smsCode": {"5555"}})
		assert.NotNil(t, res)
	}
}

func deleteSnsUser() {
	deleteSql := fmt.Sprintf("delete from users where unionId='%s'", "oFRlVwXY7GkRhpKyfjvTo6oW7kw8")
	runSql(deleteSql, true)
	deleteSql2 := fmt.Sprintf("delete from sns_users where unionId='%s'", "oFRlVwXY7GkRhpKyfjvTo6oW7kw8")
	runSql(deleteSql2, false)
}

func deleteSnsUser2() {
	deleteSql := fmt.Sprintf("delete from users where unionId='%s'", "oFRlVwXQIzb7TNDS45hQCT8MidQc")
	runSql(deleteSql, true)
	deleteSql2 := fmt.Sprintf("delete from sns_users where unionId='%s'", "oFRlVwXQIzb7TNDS45hQCT8MidQc")
	runSql(deleteSql2, false)
}

func insertSnsUser(userId string) {
	sql := fmt.Sprintf("replace into sns_users (openId, username, avatarUrl, platform, userId, unionId) values('%s','%s','%s','%s', '%s', '%s')",
		"ol0AFwFe5jFoXcQby4J7AWJaWXIM", "李智维",
		"http://wx.qlogo.cn/mmopen/NINuDc2FdYUJUPu6kmiajFweydQ5dfC2ibgOTibQQVEfj1IVnwXH7ZMRXKPvsmwLpoSk1xJIGXg6tVZrOiaCfsIeHWkCfbMAL2CH/0",
		"wechat", userId, "oFRlVwXY7GkRhpKyfjvTo6oW7kw8")
	runSql(sql, false)

	updateSql := fmt.Sprintf("update users set unionId='%s' where userId='%s'", "oFRlVwXY7GkRhpKyfjvTo6oW7kw8", userId)
	runSql(updateSql, false)
}

func insertSnsUser2(userId string) {
	sql := fmt.Sprintf("replace into sns_users (openId, username, avatarUrl, platform, userId, unionId) values('%s','%s','%s','%s', '%s', '%s')",
		"ol0AFwLgaHJ4rjhfRdUPtvBzlrt8", "李智维lzwjava",
		"http://wx.qlogo.cn/mmopen/NINuDc2FdYUJUPu6kmiajFweydQ5dfC2ibgOTibQQVEfj1IVnwXH7ZMRXKPvsmwLpoSk1xJIGXg6tVZrOiaCfsIeHWkCfbMAL2CH/0",
		"wechat", userId, "oFRlVwXQIzb7TNDS45hQCT8MidQc")
	runSql(sql, false)

	updateSql := fmt.Sprintf("update users set unionId='%s' where userId='%s'", "oFRlVwXQIzb7TNDS45hQCT8MidQc", userId)
	runSql(updateSql, false)
}

func TestWeChat_registerBySns(t *testing.T) {
	deleteSnsUser()
	c := NewClient()
	insertSnsUser("0")
	res := c.postData("users/registerBySns", url.Values{"openId": {"ol0AFwFe5jFoXcQby4J7AWJaWXIM"},
		"platform": {"wechat"}, "mobilePhoneNumber": {randomMobile()}, "smsCode": {"5555"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("userId").Interface())
}

func TestWeChat_autoBind(t *testing.T) {
	deleteSnsUser()

	c := NewClient()
	user := registerNewUser(c)
	insertSnsUser("0")

	res := c.postData("users/registerBySns", url.Values{"openId": {"ol0AFwFe5jFoXcQby4J7AWJaWXIM"},
		"platform": {"wechat"}, "mobilePhoneNumber": {user.Get("mobilePhoneNumber").MustString()}, "smsCode": {"5555"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("userId").Interface())
}

func TestWeChat_silentOauth(t *testing.T) {
	c := NewClient()
	c.get("wechat/silentOauth", url.Values{"code": {"0412dP3b0NMQXu1Yky3b0CtO3b02dP3T"}})
}

func TestWeChat_silentOauth_web(t *testing.T) {
	c, _ := NewClientAndUser()
	c.get("wechat/silentOauth", url.Values{"code": {"011JMd6j2iZUcH0H0D7j2RKe6j2JMd6b"}})
}

func TestWeChat_webOauth(t *testing.T) {
	c := NewClient()
	c.get("wechat/webOauth", url.Values{"code": {"041SrCk916g2MS1RrKn91zbCk91SrCk6"}})
}

func TestWeChat_bind(t *testing.T) {
	c, _ := NewClientAndUser()
	deleteSnsUser()
	res := c.get("wechat/bind", url.Values{"code": {"021mZiQa0Ralxu1L7kNa0RgeQa0mZiQP"}})
	assert.NotNil(t, res)
}

func TestWeChat_wxpay(t *testing.T) {
	// c, userId := NewClientAndUser()
	// insertSnsUser(userId)
	// res := c.get("wechat/wxpay", url.Values{})
	// assert.NotNil(t, res)
}

func TestWeChat_wxpayNotify(t *testing.T) {
	// c, _ := NewClientAndUser()
	// res := c.post("wechat/wxpayNotify", url.Values{})
	// assert.NotNil(t, res)
}

// func TestWeChat_valid(t *testing.T) {
// 	c := NewClient()
// 	res := c.get("wechat/valid", url.Values{})
// 	assert.NotNil(t, res)
// }

func TestWeChat_appOauth(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.get("wechat/appOauth", url.Values{"code": {"031ABkdx1MXBwd0hllbx1DQGdx1ABkdW"}})
	assert.NotNil(t, res)
}

func TestWeChat_appOauth_then_register(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.get("wechat/appOauth", url.Values{"code": {"041o6tpH01IN7j2pJPoH0T2npH0o6tp3"}})
	assert.NotNil(t, res)
	if res.Get("status").MustString() == "success" {
		loginType := res.Get("type").MustString()
		if loginType == "register" {
			result := res.Get("result").Get("snsUser")
			res := c.postData("users/registerBySns", url.Values{"openId": {result.Get("openId").MustString()},
				"platform": {"wechat_app"}, "mobilePhoneNumber": {randomMobile()}, "smsCode": {"5555"}})
			assert.NotNil(t, res)
		} else if loginType == "login" {
			user := res.Get("result").Get("user")
			assert.NotNil(t, user.Get("userId").MustInt())
		}
	}
}

func TestWeChat_isSubscribe(t *testing.T) {
	c, userId := NewClientAndWeChatUser()
	res := c.get("wechat/isSubscribe", url.Values{"userId": {userId}})
	if res.Get("status").MustString() == "success" {
		assert.NotNil(t, res.Interface())
		assert.Equal(t, res.MustInt(), 1)
	}
}

// func TestWeChat_createMenu(t *testing.T) {
// 	c := NewClient()
// 	res := c.getData("wechat/createMenu", url.Values{})
// 	assert.NotNil(t, res.Interface())
// }

func TestWeChat_getMenu(t *testing.T) {
	c := NewClient()
	res := c.get("wechat/menu", url.Values{})
	assert.NotNil(t, res.Interface())
}

func TestWeChat_sendText(t *testing.T) {
	c := NewClient()
	res := c.postWithStr("wechat/callback", `<xml><ToUserName><![CDATA[gh_0896caf2ec84]]></ToUserName>
<FromUserName><![CDATA[ol0AFwFe5jFoXcQby4J7AWJaWXIM]]></FromUserName>
<CreateTime>1482623024</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[哈喽]]></Content>
<MsgId>6367817400842503849</MsgId>
</xml>`)
	assert.NotNil(t, res)
}

func TestWeChat_subscribe(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	liveId := createLive(c)
	res := c.postWithStr("wechat/callback", fmt.Sprintf(`<xml><ToUserName><![CDATA[gh_0896caf2ec84]]></ToUserName>
<FromUserName><![CDATA[ol0AFwFe5jFoXcQby4J7AWJaWXIM]]></FromUserName>
<CreateTime>1482625995</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[subscribe]]></Event>
<EventKey><![CDATA[qrscene_{"type":"live", "liveId":%s}]]></EventKey>
</xml>`, liveId))
	assert.NotNil(t, res)

	user := c.getData("self", url.Values{})
	assert.NotNil(t, user.Interface())
}

func TestWeChat_subscribeByPacket(t *testing.T) {
	c, userId := NewClientAndWeChatUser()
	createPacket(c, userId)
	packetId := lastPacketId(c)
	res := c.postWithStr("wechat/callback", fmt.Sprintf(`<xml><ToUserName><![CDATA[gh_0896caf2ec84]]></ToUserName>
<FromUserName><![CDATA[ol0AFwFe5jFoXcQby4J7AWJaWXIM]]></FromUserName>
<CreateTime>1482625995</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[subscribe]]></Event>
<EventKey><![CDATA[qrscene_{"type":"packet", "packetId":"%s"}]]></EventKey>
</xml>`, packetId))
	assert.NotNil(t, res)

	user := c.getData("self", url.Values{})
	assert.NotNil(t, user.Interface())
}

func TestWeChat_scanEvent(t *testing.T) {
	c, userId := NewClientAndWeChatUser()
	createPacket(c, userId)
	packetId := lastPacketId(c)
	res := c.postWithStr("wechat/callback", fmt.Sprintf(`<xml><ToUserName><![CDATA[gh_0896caf2ec84]]></ToUserName>
<FromUserName><![CDATA[ol0AFwFe5jFoXcQby4J7AWJaWXIM]]></FromUserName>
<CreateTime>1482625995</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[SCAN]]></Event>
<EventKey><![CDATA[{"type":"packet", "packetId":"%s"}]]></EventKey>
</xml>`, packetId))
	assert.NotNil(t, res)

	user := c.getData("self", url.Values{})
	assert.NotNil(t, user.Interface())
}

func TestWeChat_fixAllSubscribe(t *testing.T) {
	c := NewClient()
	c.admin = true
	res := c.getData("wechat/fixAllSubscribe", url.Values{})
	assert.NotNil(t, res)
}

func TestWeChat_qrcode(t *testing.T) {
	c := NewClient()
	res := c.get("wechat/qrcode", url.Values{"type": {"packet"}, "packetId": {"abc"}})
	assert.NotNil(t, res.Interface())
}

func TestWeChat_login(t *testing.T) {
	c := NewClient()
	res := c.post("wechat/login", url.Values{"code": {"abc"}})
	assert.NotNil(t, res.Interface())
}

func TestWeChat_registerByApp(t *testing.T) {
	c := NewClient()
	loginRes := c.postData("wechat/login", url.Values{"code": {"abc"}})
	res := c.post("wechat/registerByApp", url.Values{
		"thirdSession":  {loginRes.Get("thirdSession").MustString()},
		"rawData":       {"{\"nickName\":\"李智维-趣直播\",\"gender\":1,\"language\":\"zh_CN\",\"city\":\"\",\"province\":\"Beijing\",\"country\":\"CN\",\"avatarUrl\":\"http://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTLyxI8Q0XqyejNIaMaJSofP2sN5z0xg0FibXmGMBtlTEkKxuoLJzktAO3wUMfJlPHfDZH3GQvPfm0A/0\"}"},
		"signature":     {"5ec88de31ce137f7407e7b19d72b0fafc2000511"},
		"iv":            {"eYpTfDRT8pSnIDwpKHdFvA=="},
		"encryptedData": {"U8K3mOnUpDIHhqMreVCrzpr1RGww4U0C56Z7fTZpeqIZ74GzFe8Z5doMncvNZmTbuMfz2BfPOAforw2rkxYm91/K4VESeOLuAuFdQIlu55kaQS8XRp5bZy/Sn5smy1bq80r2+Iy2TmA1cfnHCektRYJRyccYGuE2I5kRD+EKsAoScLgS5heLHpyFfvZgmJYs6vREZEmJ7NDFTXoAqqUBcwbTBnF3WefCJdtJf+cHGIK1BiybV/is4vrzJjK08pUKRUz4XVMkejK4ve9w8sIafp9xSnHXaEU2KfFC8ZhEdA4NgQcZm9AT2Nif8oUGCoaI90vtjua1Z7uWZqkywkjgIo6c2STxUJ2YPAavjJYxyjBsZ+i+82KUV9ElLaJ0BeXFni6A56sq3pougEePoIfD9YszQm0V3y1bNY/Nys9HWHMntkzItBMie6fj6A4c+h6MqXOTvIsmSKLLjmzniuNrswrOuOj/QAs48udCCbs3MhE="}})
	assert.NotNil(t, res.Interface())
}

func TestWeChat_loginBySession(t *testing.T) {
	c := NewClient()
	res := c.postData("wechat/login", url.Values{"code": {"abc"}})
	assert.NotNil(t, res.Interface())
	loginRes := c.post("wechat/loginBySession", url.Values{"thirdSession": {res.Get("thirdSession").MustString()}})
	assert.NotNil(t, loginRes.Interface())
}
