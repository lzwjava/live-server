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
	c, _ := NewClientAndUser()
	res := c.get("wechat/oauth", url.Values{"code": {"0013U0ps1XUkNq0Dtels17A0ps13U0pH"}})
	assert.NotNil(t, res)
}

func TestWeChat_oauth_then_register(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.get("wechat/oauth", url.Values{"code": {"0211P1Ji0CVBhk1wdnJi0Bu2Ji01P1J8"}})
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
	c.get("wechat/silentOauth", url.Values{"code": {"021nmcVB0w3SWd2oIwTB0RCbVB0nmcVI"}})
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
	assert.True(t, res.MustBool())
}
