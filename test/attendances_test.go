package liveserver

import (
	"net/url"
	"testing"

	"fmt"

	"strings"

	"github.com/bitly/go-simplejson"
	"github.com/stretchr/testify/assert"
)

func TestAttendances_onlyCreate(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}})
	assert.NotNil(t, res)
	_, exists := res.CheckGet("status")
	assert.False(t, exists)
}

func parseOrderNo(res *simplejson.Json) string {
	dataString := res.MustString()
	strArr := strings.Split(dataString, "&")
	var orderNo string
	for _, str := range strArr {
		arr := strings.Split(str, "=")
		if arr[0] == "out_trade_no" {
			orderNo = arr[1][1 : len(arr[1])-1]
		}
	}
	return orderNo
}

func TestAttendances_create(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}})
	assert.NotNil(t, res)
	_, exists := res.CheckGet("status")
	assert.False(t, exists)
	orderNo := parseOrderNo(res)
	callbackStr := liveCallbackStr(orderNo)
	callbackRes := c2.postWithStr("rewards/notify", callbackStr)
	assert.NotNil(t, callbackRes)
	assert.Equal(t, callbackRes, "success")
}

func createAttendance(c *Client, user *simplejson.Json, liveId string) {
	res := c.postData("attendances", url.Values{"liveId": {liveId}})

	orderNo := parseOrderNo(res)
	callbackStr := liveCallbackStr(orderNo)
	c.postWithStr("rewards/notify", callbackStr)
}

func callbackStr(orderNo string) string {
	const jsonStream = `discount=0.00&payment_type=1&subject=15245参加直播92&trade_no=2016082521001004950207073962
	&buyer_email=lzwjava@gmail.com&gmt_create=2016-08-25
	18:00:37¬ify_type=trade_status_sync&quantity=1&out_trade_no=%s&seller_id=2088421737526755¬ify_time=2016-08-25 18:00:38&body=15245 参加 C++ 编程&trade_status=TRADE_SUCCESS&is_total_fee_adjust=N&total_fee=10.00&gmt_payment=2016-08-25 18:00:37&seller_email=finance@quzhiboapp.com&price=10.00&buyer_id=2088402019259954¬ify_id=632e791af93ecd47ad3cac1d13895bfnby&use_coupon=N&sign_type=RSA&sign=Tg02aD2wq4jb99fpeEO5B4DaMN5DsGujmWoAF7BNbHpbXRlGKOOVUlt+V9OHfCH8tn8/2jHeAfyLV04IO7hn9Xi0rBdb0xwGwce2dEHOINLl/bbI5GeOaR8R/HPDxoIThhNhHxY8ektDt33CBR4Es8MgqEGwCkYoAdgjSMV9DdU=`
	out := fmt.Sprintf(jsonStream, orderNo)
	return out
}

func liveCallbackStr(orderNo string) string {
	return callbackStr(orderNo)
}

func TestAttendances_liveList(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, user := NewClientAndUser()
	createAttendance(c2, user, liveId)

	res := c.getData("attendances/lives/"+liveId, url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, len(res.MustArray()), 1)
}

func TestAttendances_list(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, user := NewClientAndUser()
	createAttendance(c2, user, liveId)

	res := c2.getData("attendances/me", url.Values{})
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

func TestAttendances_oneByLiveId(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, user := NewClientAndUser()

	res := c2.get("attendances/one", url.Values{"liveId": {liveId}})
	assert.Equal(t, res.Get("status").MustString(), "object_not_exists")

	createAttendance(c2, user, liveId)

	attendance := c2.getData("attendances/one", url.Values{"liveId": {liveId}})
	assert.NotNil(t, attendance)
}
