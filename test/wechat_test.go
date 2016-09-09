package liveserver

import (
	"fmt"
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestWeChat_sign(t *testing.T) {
	c := NewClient()
	res := c.getData("wechat/sign", url.Values{})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("appId").Interface())
	assert.NotNil(t, res.Get("nonceStr").Interface())
}

func TestWeChat_register(t *testing.T) {
	c := NewClient()
	res := c.post("wechat/register", url.Values{"code": {"001ONMjt1kPSE806Edjt18MSjt1ONMjS"}})
	assert.NotNil(t, res)
}

func TestWeChat_registerBySns(t *testing.T) {
	c := NewClient()
	sql := fmt.Sprintf("replace into sns_users (openId, username, avatarUrl, platform) values('%s','%s','%s','%s')",
		"ol0AFwFe5jFoXcQby4J7AWJaWXIM", "李智维",
		"http://wx.qlogo.cn/mmopen/NINuDc2FdYUJUPu6kmiajFweydQ5dfC2ibgOTibQQVEfj1IVnwXH7ZMRXKPvsmwLpoSk1xJIGXg6tVZrOiaCfsIeHWkCfbMAL2CH/0",
		"wechat")
	runSql(sql, false)
	res := c.postData("users/registerBySns", url.Values{"openId": {"ol0AFwFe5jFoXcQby4J7AWJaWXIM"},
		"platform": {"wechat"}, "mobilePhoneNumber": {randomMobile()}, "smsCode": {"5555"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("userId").Interface())
}

func TestWeChat_silentOauth(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	hash := createState(c, liveId)
	c.get("wechat/silentOauth", url.Values{"code": {"001EoSyU0GATOR1nc9zU0qrTyU0EoSyB"}, "state": {hash}})
}