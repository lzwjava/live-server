package liveserver

import (
	"fmt"
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestRewards_create(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, userId := NewClientAndWeChatUser()
	createWechatAttendance(c2, userId, liveId)

	res := c2.post("rewards", url.Values{"liveId": {liveId},
		"amount": {"1000"}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res.Interface())
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	fmt.Println("callbackRes:" + callbackRes)
	assert.NotNil(t, callbackRes)
}

func reward(c *Client, userId string, liveId string) {
	c.post("rewards", url.Values{"liveId": {liveId},
		"amount": {"1000"}, "channel": {"wechat_h5"}})
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	c.postWithStr("wechat/wxpayNotify", callbackStr)
}

func TestRewards_list(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, userId := NewClientAndWeChatUser()
	createWechatAttendance(c2, userId, liveId)
	reward(c2, userId, liveId)

	rewards := c2.getData("lives/"+liveId+"/rewards", url.Values{})
	assert.Equal(t, len(rewards.MustArray()), 1)
}
