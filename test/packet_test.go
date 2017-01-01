package liveserver

import (
	"fmt"
	"net/url"
	"testing"
	"time"

	"github.com/stretchr/testify/assert"
)

func TestPackets_create(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)

	res := c2.post("packets", url.Values{"totalAmount": {"1000"},
		"totalCount": {"1"}, "channel": {"wechat_h5"}, "wishing": {"新年快乐"}})
	assert.NotNil(t, res.Interface())
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	fmt.Println("callbackRes:" + callbackRes)
	assert.NotNil(t, callbackRes)
}

func TestPackets_create_grab(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)

	createPacket(c2, userId)

	packet := c2.getData("packets/me", url.Values{})

	grabRes := c2.getData("packets/"+packet.Get("packetId").MustString()+"/grab", url.Values{})
	assert.NotNil(t, grabRes)
}

func TestPackets_create_grabTwice(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)

	createPacket(c2, userId)

	packet := c2.getData("packets/me", url.Values{})

	grabRes := c2.getData("packets/"+packet.Get("packetId").MustString()+"/grab", url.Values{})
	assert.NotNil(t, grabRes)

	c1, userId2 := NewClientAndUser()
	insertSnsUser2(userId2)

	grabRes = c1.getData("packets/"+packet.Get("packetId").MustString()+"/grab", url.Values{})
	assert.NotNil(t, grabRes)
}

func createPacket(c2 *Client, userId string) {
	c2.post("packets", url.Values{"totalAmount": {"1000"},
		"totalCount": {"2"}, "channel": {"wechat_h5"}, "wishing": {"新年快乐"}})
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	c2.postWithStr("wechat/wxpayNotify", callbackStr)
}

func TestPackets_one(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)
	createPacket(c2, userId)
	<-time.After(1 * time.Second)
	packetId := lastPacketId(c2)
	getRes := c2.getData("packets/"+packetId, url.Values{})
	assert.NotNil(t, getRes.Interface())
}

func lastPacketId(c2 *Client) string {
	packet := c2.getData("packets/me", url.Values{})
	packetId := packet.Get("packetId").MustString()
	if len(packetId) == 0 {
		panic("packetId is null")
	}
	return packetId
}

func grabPacket(c2 *Client, packetId string) {
	c2.getData("packets/"+packetId+"/grab", url.Values{})
}

func TestPackets_userPacket(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)
	createPacket(c2, userId)
	packetId := lastPacketId(c2)
	grabPacket(c2, packetId)
	getRes := c2.getArrayData("packets/"+packetId+"/userPackets", url.Values{})
	assert.True(t, len(getRes.MustArray()) > 0)
}

func TestPackets_meAll(t *testing.T) {
	c2, userId := NewClientAndUser()
	insertSnsUser(userId)
	createPacket(c2, userId)
	packetId := lastPacketId(c2)
	grabPacket(c2, packetId)
	getRes := c2.getArrayData("packets/meAll", url.Values{})
	assert.True(t, len(getRes.MustArray()) > 0)
}
