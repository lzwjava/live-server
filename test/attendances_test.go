package liveserver

import (
	"net/url"
	"testing"

	"encoding/json"
	"fmt"

	"github.com/bitly/go-simplejson"
	"github.com/stretchr/testify/assert"
)

func TestAttendances_create(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, user := NewClientAndUser()
	userId := toStr(user.Get("userId").MustInt())
	res := c2.postData("attendances", url.Values{"liveId": {liveId}})
	assert.NotNil(t, res)
	_, exists := res.CheckGet("status")
	assert.False(t, exists)

	orderNo := res.Get("order_no").MustString()
	callbackStr := liveCallbackStr(orderNo, liveId, userId, 5000)
	callbackRes := c2.postWithStr("rewards/callback", callbackStr)
	assert.NotNil(t, callbackRes)
}

func createAttendance(c *Client, user *simplejson.Json, liveId string) {
	userId := toStr(user.Get("userId").MustInt())
	res := c.postData("attendances", url.Values{"liveId": {liveId}})

	orderNo := res.Get("order_no").MustString()
	callbackStr := liveCallbackStr(orderNo, liveId, userId, 5000)
	c.postWithStr("rewards/callback", callbackStr)
}

func callbackStr(orderNo string, metaData map[string]interface{}, amount int) string {
	metaString, err := json.Marshal(metaData)
	checkErr(err)
	const jsonStream = `{ "id": "evt_ugB6x3K43D16wXCcqbplWAJo", "created": 1427555101, "livemode": true, "type":"charge.succeeded", "data": { "object": { "id": "ch_Xsr7u35O3m1Gw4ed2ODmi4Lw", "object": "charge", "created":
	1427555076, "livemode": true, "paid": true, "refunded": false, "app":"app_1Gqj58ynP0mHeX1q","channel":
	"upacp", "metadata": %s, "order_no": "%s", "client_ip": "127.0.0.1", "amount": %d, "amount_settle":
	0, "currency": "cny", "subject": "Your Subject", "body": "Your Body", "extra": {}, "time_paid": 1427555101, "time_expire": 1427641476, "time_settle": null, "transaction_no": "1224524301201505066067849274", "refunds": { "object": "list", "url": "/v1/charges/ch_L8qn10mLmr1GS8e5OODmHaL4/refunds", "has_more": false, "data": [] }, "amount_refunded": 0, "failure_code": null, "failure_msg": null, "credential": {}, "description": null } }, "object": "event", "pending_webhooks": 0, "request": "iar_qH4y1KbTy5eLGm1uHSTS00s" }`
	out := fmt.Sprintf(jsonStream, metaString, orderNo, amount)
	return out
}

func liveCallbackStr(orderNo string, liveId string, userId string, amount int) string {
	meta := map[string]interface{}{"liveId": liveId, "userId": userId}
	return callbackStr(orderNo, meta, amount)
}

func TestAttendances_liveList(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, user := NewClientAndUser()
	createAttendance(c2, user, liveId)

	res := c.getData("lives/"+liveId+"/attendances", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, len(res.MustArray()), 1)
}

func TestAttendances_list(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, user := NewClientAndUser()
	createAttendance(c2, user, liveId)

	res := c2.getData("attendances", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, len(res.MustArray()), 1)
}

func TestAttendances_count(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, user := NewClientAndUser()
	createAttendance(c2, user, liveId)

	live := getLive(c, liveId)
	assert.Equal(t, live.Get("attendanceCount").MustInt(), 1)
}
