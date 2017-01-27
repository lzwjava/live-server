package liveserver

import (
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
	c2, userId2 := NewClientAndWeChatUser()
	insertAppSnsUser(userId2)
	res := c2.postData("charges", url.Values{"channel": {"wechat_h5"}, "amount": {"600"}})
	assert.NotNil(t, res)
}
