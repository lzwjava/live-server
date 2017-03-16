package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestWithdraws_create(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	liveId := createLiveWithAmount(c, 20000)
	subscribeWechat(c)
	beginAndFinshLive(c, liveId)

	c2, userId := NewClientAndWeChatUser2()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	assert.NotNil(t, callbackRes)

	withdrawRes := c.postData("withdraws", url.Values{"amount": {"10000"}})
	withdrawId := toStr(withdrawRes.Get("withdrawId").MustInt())
	assert.NotNil(t, withdrawId)

	c3 := NewClient()
	c3.admin = true
	agreeRes := c3.get("withdraws/"+withdrawId+"/agree", url.Values{})
	assert.NotNil(t, agreeRes.Interface())
}

func TestWithdraws_auto(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	liveId := createLiveWithAmount(c, 5900)
	subscribeWechat(c)
	beginAndFinshLive(c, liveId)

	c2, userId := NewClientAndWeChatUser2()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	assert.NotNil(t, callbackRes)

	withdrawRes := c.postData("withdraws", url.Values{"amount": {"500"}})
	withdrawId := toStr(withdrawRes.Get("withdrawId").MustInt())
	assert.NotNil(t, withdrawId)
}

func TestWithdraws_list(t *testing.T) {
	c := NewClient()
	c.admin = true
	withdraws := c.getData("withdraws", url.Values{})
	assert.NotNil(t, withdraws.Interface())
}

func TestWithdraws_createByManul(t *testing.T) {
	c, userId2 := NewClientAndWeChatUser()
	liveId := createLiveWithAmount(c, 5900)
	subscribeWechat(c)
	// beginAndFinshLive(c, liveId)

	c2, userId := NewClientAndWeChatUser2()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	assert.NotNil(t, callbackRes)

	c3 := NewClient()
	c3.admin = true
	withdrawRes := c3.postData("withdraws/manual", url.Values{"amount": {"500"}, "userId": {userId2},
		"transfer": {"0"}})
	withdrawId := toStr(withdrawRes.Get("withdrawId").MustInt())
	assert.NotNil(t, withdrawId)
}

func TestWithdraws_all(t *testing.T) {
	c := NewClient()
	c.admin = true
	res := c.getData("withdraws/withdrawAll", url.Values{})
	assert.NotNil(t, res.Interface())
}
