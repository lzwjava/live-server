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

func insertSnsUser(userId string) {
	sql := fmt.Sprintf("replace into sns_users (openId, username, avatarUrl, platform, userId, unionId) values('%s','%s','%s','%s', '%s', '%s')",
		"ol0AFwFe5jFoXcQby4J7AWJaWXIM", "李智维",
		"http://wx.qlogo.cn/mmopen/NINuDc2FdYUJUPu6kmiajFweydQ5dfC2ibgOTibQQVEfj1IVnwXH7ZMRXKPvsmwLpoSk1xJIGXg6tVZrOiaCfsIeHWkCfbMAL2CH/0",
		"wechat", userId, "oFRlVwXY7GkRhpKyfjvTo6oW7kw8")
	runSql(sql, false)
}

func TestWeChat_registerBySns(t *testing.T) {
	c := NewClient()
	insertSnsUser("0")
	res := c.postData("users/registerBySns", url.Values{"openId": {"ol0AFwFe5jFoXcQby4J7AWJaWXIM"},
		"platform": {"wechat"}, "mobilePhoneNumber": {randomMobile()}, "smsCode": {"5555"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("userId").Interface())
}

func TestWeChat_silentOauth(t *testing.T) {
	c := NewClient()
	c.get("wechat/silentOauth", url.Values{"code": {"021Lk67R0nLLPa2PbA7R0fFa7R0Lk670"}})
}

func TestWeChat_silentOauth_web(t *testing.T) {
	c, _ := NewClientAndUser()
	c.get("wechat/silentOauth", url.Values{"code": {"011JMd6j2iZUcH0H0D7j2RKe6j2JMd6b"}})
}

func TestWeChat_webOauth(t *testing.T) {
	c := NewClient()
	c.get("wechat/webOauth", url.Values{"code": {"041SrCk916g2MS1RrKn91zbCk91SrCk6"}})
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
