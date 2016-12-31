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
	res := c.get("wechat/oauth", url.Values{"code": {"041JWYHN1lcu741DmrHN1Tq9IN1JWYHj"}})
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
	deleteSnsUser()
	sql := fmt.Sprintf("replace into sns_users (openId, username, avatarUrl, platform, userId, unionId) values('%s','%s','%s','%s', '%s', '%s')",
		"ol0AFwFe5jFoXcQby4J7AWJaWXIM", "李智维",
		"http://wx.qlogo.cn/mmopen/NINuDc2FdYUJUPu6kmiajFweydQ5dfC2ibgOTibQQVEfj1IVnwXH7ZMRXKPvsmwLpoSk1xJIGXg6tVZrOiaCfsIeHWkCfbMAL2CH/0",
		"wechat", userId, "oFRlVwXY7GkRhpKyfjvTo6oW7kw8")
	runSql(sql, false)

	updateSql := fmt.Sprintf("update users set unionId='%s' where userId='%s'", "oFRlVwXY7GkRhpKyfjvTo6oW7kw8", userId)
	runSql(updateSql, false)
}

func insertSnsUser2(userId string) {
	deleteSnsUser2()
	sql := fmt.Sprintf("replace into sns_users (openId, username, avatarUrl, platform, userId, unionId) values('%s','%s','%s','%s', '%s', '%s')",
		"oFRlVwXQIzb7TNDS45hQCT8MidQc", "李智维",
		"http://wx.qlogo.cn/mmopen/NINuDc2FdYUJUPu6kmiajFweydQ5dfC2ibgOTibQQVEfj1IVnwXH7ZMRXKPvsmwLpoSk1xJIGXg6tVZrOiaCfsIeHWkCfbMAL2CH/0",
		"wechat", userId, "oFRlVwXQIzb7TNDS45hQCT8MidQc")
	runSql(sql, false)

	updateSql := fmt.Sprintf("update users set unionId='%s' where userId='%s'", "oFRlVwXQIzb7TNDS45hQCT8MidQc", userId)
	runSql(updateSql, false)
}

func TestWeChat_registerBySns(t *testing.T) {
	c := NewClient()
	insertSnsUser("0")
	res := c.postData("users/registerBySns", url.Values{"openId": {"ol0AFwFe5jFoXcQby4J7AWJaWXIM"},
		"platform": {"wechat"}, "mobilePhoneNumber": {randomMobile()}, "smsCode": {"5555"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("userId").Interface())
}

func TestWeChat_autoBind(t *testing.T) {
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
	c, userId := NewClientAndUser()
	insertSnsUser(userId)
	res := c.getData("wechat/isSubscribe", url.Values{"userId": {userId}})
	assert.NotNil(t, res.Interface())
	assert.Equal(t, res.MustInt(), 1)
}

// func TestWeChat_createMenu(t *testing.T) {
// 	c := NewClient()
// 	res := c.getData("wechat/createMenu", url.Values{})
// 	assert.NotNil(t, res.Interface())
// }

func TestWeChat_getMenu(t *testing.T) {
	c := NewClient()
	res := c.getData("wechat/menu", url.Values{})
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
	c, userId := NewClientAndUser()
	insertSnsUser(userId)
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
	c, userId := NewClientAndUser()
	insertSnsUser(userId)
	createPacket(c)
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

func TestWeChat_fixAllSubscribe(t *testing.T) {
	c := NewClient()
	c.admin = true
	res := c.getData("wechat/fixAllSubscribe", url.Values{})
	assert.NotNil(t, res)
}

func TestWeChat_qrcode(t *testing.T) {
	c := NewClient()
	res := c.getData("wechat/qrcode", url.Values{"type": {"packet"}, "packetId": {"abc"}})
	assert.NotNil(t, res.Interface())
}
