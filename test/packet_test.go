package liveserver

import (
	"fmt"
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestPackets_create(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)

	res := c2.post("packets", url.Values{"totalAmount": {"1000"},
		"totalCount": {"1"}, "channel": {"wechat_h5"}, "wishing": {"新年快乐"}})
	assert.NotNil(t, res.Interface())
	orderNo := getLastOrderNo()
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	fmt.Println("callbackRes:" + callbackRes)
	assert.NotNil(t, callbackRes)
}

func TestPackets_create_grab(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)

	res := c2.post("packets", url.Values{"totalAmount": {"1000"},
		"totalCount": {"1"}, "channel": {"wechat_h5"}, "wishing": {"新年快乐"}})
	assert.NotNil(t, res.Interface())
	orderNo := getLastOrderNo()
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	fmt.Println("callbackRes:" + callbackRes)
	assert.NotNil(t, callbackRes)

	packet := c2.getData("packets/me", url.Values{})

	grabRes := c2.getData("packets/"+packet.Get("packetId").MustString()+"/grab", url.Values{})
	assert.NotNil(t, grabRes)
}

func TestPackets_create_grabTwice(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)

	res := c2.post("packets", url.Values{"totalAmount": {"1000"},
		"totalCount": {"2"}, "channel": {"wechat_h5"}, "wishing": {"新年快乐"}})
	assert.NotNil(t, res.Interface())
	orderNo := getLastOrderNo()
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	fmt.Println("callbackRes:" + callbackRes)
	assert.NotNil(t, callbackRes)

	packet := c2.getData("packets/me", url.Values{})

	grabRes := c2.getData("packets/"+packet.Get("packetId").MustString()+"/grab", url.Values{})
	assert.NotNil(t, grabRes)

	c1, userId2 := NewClientAndUser()
	insertSnsUser2(userId2)

	grabRes = c1.getData("packets/"+packet.Get("packetId").MustString()+"/grab", url.Values{})
	assert.NotNil(t, grabRes)
}

func TestPackets_one(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)

	res := c2.post("packets", url.Values{"totalAmount": {"1000"},
		"totalCount": {"1"}, "channel": {"wechat_h5"}, "wishing": {"新年快乐"}})
	assert.NotNil(t, res.Interface())
	orderNo := getLastOrderNo()
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	fmt.Println("callbackRes:" + callbackRes)
	assert.NotNil(t, callbackRes)

	packet := c2.getData("packets/me", url.Values{})
	getRes := c2.getData("packets/"+packet.Get("packetId").MustString(), url.Values{})
	assert.NotNil(t, getRes.Interface())
}
