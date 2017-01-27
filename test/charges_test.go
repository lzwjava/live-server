package liveserver

import (
	"fmt"
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestCharges_remark(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLiveWithAmount(c, 5900)

	c2, userId := NewClientAndWeChatUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	orderNo := getLastOrderNo(userId)
	remarkRes := c2.postData("charges/remark", url.Values{"orderNo": {orderNo}, "remark": {"支付失败"}})
	assert.NotNil(t, remarkRes.Interface())
}

func TestCharges_onlyCreateByWeChat(t *testing.T) {
	c2, _ := NewClientAndWeChatUser()
	res := c2.postData("charges", url.Values{"channel": {"wechat_h5"}, "amount": {"600"}})
	assert.NotNil(t, res)
}

func TestCharges_createByWeChat(t *testing.T) {
	c2, userId := NewClientAndWeChatUser()
	res := c2.postData("charges", url.Values{"amount": {"600"}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	fmt.Println("callbackRes:" + callbackRes)
	assert.NotNil(t, callbackRes)

	account := c2.getData("accounts/me", url.Values{})
	assert.Equal(t, account.Get("balance").MustInt(), 600)
}

func TestCharges_onlyCreateByAppleIAP(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.postData("charges", url.Values{"amount": {"600"}, "channel": {"apple_iap"}})
	assert.NotNil(t, res.Interface())
	assert.NotNil(t, res.Get("orderNo").MustString())
}

func TestCharges_iapCallback(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.postData("charges", url.Values{"amount": {"600"}, "channel": {"apple_iap"}})
	assert.NotNil(t, res.Interface())
	orderNo := res.Get("orderNo").MustString()
	callbackRes := c.postData("charges/appleCallback", url.Values{"orderNo": {orderNo}, "receipt": {"abc"}})
	assert.NotNil(t, callbackRes.Interface())

	account := c.getData("accounts/me", url.Values{})
	assert.Equal(t, account.Get("balance").MustInt(), 600)
}
