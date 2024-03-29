package liveserver

import (
	"net/url"
	"testing"
	"time"

	"fmt"

	"strings"

	"github.com/bitly/go-simplejson"
	"github.com/stretchr/testify/assert"
)

func TestAttendances_onlyCreate(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"alipay_app"}})
	assert.NotNil(t, res)
	_, exists := res.CheckGet("status")
	assert.False(t, exists)
}

func TestAttendances_onlyCreateByWeChat(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndWeChatUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("appId").Interface())
}

func TestAttendances_onlyCreateByWeChatApp(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, userId2 := NewClientAndWeChatUser()
	insertAppSnsUser(userId2)
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_app"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("appId").Interface())
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
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"alipay_app"}})
	assert.NotNil(t, res)
	_, exists := res.CheckGet("status")
	assert.False(t, exists)
	orderNo := parseOrderNo(res)
	callbackStr := liveCallbackStr(orderNo)
	callbackRes := c2.postWithStr("rewards/notify", callbackStr)
	assert.NotNil(t, callbackRes)
	assert.Equal(t, callbackRes, "success")
}

func TestAttendances_createNoNeedPay(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLiveNotNeedPay(c)

	c2, _ := NewClientAndUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}})
	assert.NotNil(t, res.Interface())

	lv := c2.getData("lives/"+liveId, url.Values{})
	assert.NotNil(t, lv.Get("rtmpKey").MustString())
}

func TestAttendances_createByWeChat(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLiveWithAmount(c, 5900)

	c2, userId := NewClientAndWeChatUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	wxpayNotifyLastOrderNo(c2, userId)
}

func wxpayNotifyLastOrderNo(c *Client, userId string) {
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	c.postWithStr("wechat/wxpayNotify", callbackStr)
}

func TestAttendances_createByWeChatInviteNoNotify(t *testing.T) {
	c3, userId3 := NewClientAndWeChatUser2()
	c3.postData("self", url.Values{"incomeSubscribe": {"0"}})

	c, _ := NewClientAndUser()
	liveId := createLiveWithAmount(c, 5900)

	c2, userId := NewClientAndWeChatUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId},
		"channel": {"wechat_h5"}, "fromUserId": {userId3}})
	assert.NotNil(t, res)
	wxpayNotifyLastOrderNo(c2, userId)
}

func TestAttendances_createByWeChatInvite(t *testing.T) {
	_, userId3 := NewClientAndWeChatUser2()

	c, _ := NewClientAndUser()
	liveId := createLiveWithAmount(c, 5900)

	c2, userId := NewClientAndWeChatUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId},
		"channel": {"wechat_h5"}, "fromUserId": {userId3}})
	assert.NotNil(t, res)
	wxpayNotifyLastOrderNo(c2, userId)
}

func TestAttendances_inviteList(t *testing.T) {
	c3, userId3 := NewClientAndUser()

	c, _ := NewClientAndUser()
	liveId := createLiveWithAmount(c, 5900)

	createAttendance(c3, liveId)

	c2, userId2 := NewClientAndWeChatUser()
	createWechatAttendanceWithFrom(c2, userId2, liveId, userId3)

	c4, userId4 := NewClientAndWeChatUser2()
	createWechatAttendanceWithFrom(c4, userId4, liveId, userId3)

	invites := c2.getData("attendances/invites", url.Values{"liveId": {liveId}, "limit": {"10"}})
	assert.NotNil(t, invites.MustArray())
}

func TestAttendances_createByWeChatWithCoupon(t *testing.T) {
	adminC := NewClient()
	adminC.admin = true

	c, _ := NewClientAndUser()
	liveId := createLiveWithAmount(c, 5900)

	deleteSnsUser()
	c2 := NewClient()
	user := registerNewUser(c2)
	userId := toStr(user.Get("userId").MustInt())
	insertSnsUser(userId)

	postRes := adminC.postData("coupons", url.Values{
		"phone":  {user.Get("mobilePhoneNumber").MustString()},
		"liveId": {liveId}})
	assert.NotNil(t, postRes.Interface())

	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	wxpayNotifyLastOrderNo(c, userId)
}

func TestAttendances_createByWeChatByStaff(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLiveWithAmount(c, 5900)

	deleteSnsUser()
	c2 := NewClient()
	user := registerNewUser(c2)
	userId := toStr(user.Get("userId").MustInt())
	insertSnsUser(userId)

	createStaff(c2)

	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	orderNo := getLastOrderNo(userId)
	wxpayNotifyLastOrderNo(c2, userId)

	charge := c2.getData("charges/one", url.Values{"orderNo": {orderNo}})
	assert.NotNil(t, charge.Interface())
	assert.Equal(t, charge.Get("amount").MustInt(), 1)
}

func TestAttendances_createByWeChatQrcode(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndWeChatUser()
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_qrcode"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("code_url"))
	assert.NotNil(t, res.Get("orderNo"))
	orderNo := res.Get("orderNo").MustString()
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c2.postWithStr("wechat/wxpayNotify", callbackStr)
	assert.NotNil(t, callbackRes)
}

func TestAttendances_createByWeChat_withShare(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLiveWithAmount(c, 1000)

	c2, userId := NewClientAndWeChatUser()
	createShare(c2, liveId)
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	wxpayNotifyLastOrderNo(c2, userId)
}

func TestAttendances_createByWeChat_withShare_AmountLittle(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLiveWithAmount(c, 100)

	c2, userId := NewClientAndWeChatUser()
	createShare(c2, liveId)
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"}})
	assert.NotNil(t, res)
	wxpayNotifyLastOrderNo(c2, userId)
}

func TestAttendances_CreateByWeChatApp(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	deleteAppSnsUser()
	c2, userId2 := NewClientAndWeChatUser()
	insertAppSnsUser(userId2)
	res := c2.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_app"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("appId").Interface())
	wxpayNotifyLastOrderNo(c2, userId2)
}

func getLastOrderNo(userId string) string {
	<-time.After(100 * time.Millisecond)
	sql := fmt.Sprintf("select orderNo from charges where creator=%s order by created desc limit 1", userId)
	fmt.Println(sql)
	rows := queryDb(sql)
	defer rows.Close()
	rows.Next()
	var orderNo string
	rows.Scan(&orderNo)
	err := rows.Err()
	checkErr(err)
	return orderNo
}

func createAttendance(c *Client, liveId string) {
	res := c.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"alipay_app"}})

	orderNo := parseOrderNo(res)
	callbackStr := liveCallbackStr(orderNo)
	c.postWithStr("rewards/notify", callbackStr)
}

func createWechatAttendanceWithFrom(c *Client, userId string, liveId string, fromUserId string) {
	c.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_h5"},
		"fromUserId": {fromUserId}})
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c.postWithStr("wechat/wxpayNotify", callbackStr)
	fmt.Println("callbackRes:" + callbackRes)
}

func createWechatAttendance(c *Client, userId string, liveId string) {
	createWechatAttendanceWithFrom(c, userId, liveId, "")
}

func createWechatAppAttendance(c *Client, userId string, liveId string) {
	c.postData("attendances", url.Values{"liveId": {liveId}, "channel": {"wechat_app"}})
	orderNo := getLastOrderNo(userId)
	callbackStr := wechatCallbackStr(orderNo)
	callbackRes := c.postWithStr("wechat/wxpayNotify", callbackStr)
	fmt.Println("callbackRes:" + callbackRes)
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

func wechatCallbackStr(orderNo string) string {
	str := `<xml><appid><![CDATA[wx7b5f277707699557]]></appid>
<bank_type><![CDATA[BOC_DEBIT]]></bank_type>
<cash_fee><![CDATA[1000]]></cash_fee>
<fee_type><![CDATA[CNY]]></fee_type>
<is_subscribe><![CDATA[Y]]></is_subscribe>
<mch_id><![CDATA[1387703002]]></mch_id>
<nonce_str><![CDATA[uam0p1f6wie432svf6kiz5fg74nz1ra4]]></nonce_str>
<openid><![CDATA[ol0AFwFe5jFoXcQby4J7AWJaWXIM]]></openid>
<out_trade_no><![CDATA[%s]]></out_trade_no>
<result_code><![CDATA[SUCCESS]]></result_code>
<return_code><![CDATA[SUCCESS]]></return_code>
<sign><![CDATA[FE28FD8B4060F23C64D0A2B2C140493B]]></sign>
<time_end><![CDATA[20160914000111]]></time_end>
<total_fee>1000</total_fee>
<trade_type><![CDATA[JSAPI]]></trade_type>
<transaction_id><![CDATA[4002972001201609143886176463]]></transaction_id>
</xml>`
	return fmt.Sprintf(str, orderNo)
}

func TestAttendances_liveList(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	createAttendance(c2, liveId)

	res := c.getData("attendances/lives/"+liveId, url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, len(res.MustArray()), 1)
}

func TestAttendances_list(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	createAttendance(c2, liveId)

	res := c2.getData("attendances/me", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, len(res.MustArray()), 1)
}

func TestAttendances_count(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	createAttendance(c2, liveId)

	live := getLive(c, liveId)
	assert.Equal(t, live.Get("attendanceCount").MustInt(), 1)
}

func TestAttendances_oneByLiveId(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()

	res := c2.get("attendances/one", url.Values{"liveId": {liveId}})
	assert.Equal(t, res.Get("status").MustString(), "object_not_exists")

	createAttendance(c2, liveId)

	attendance := c2.getData("attendances/one", url.Values{"liveId": {liveId}})
	assert.NotNil(t, attendance)
}

func TestAttendances_attendanceId(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	createAttendance(c2, liveId)

	live := getLive(c2, liveId)
	assert.NotEqual(t, live.Get("attendanceId").MustInt(), 0)
}

// func TestAttendances_refund(t *testing.T) {
// 	c, _ := NewClientAndUser()
// 	liveId := createLive(c)
//
// 	c2, userId := NewClientAndWeChatUser()
// 	createWechatAttendance(c2, userId, liveId)
//
// 	c.admin = true
// 	res := c.get("attendances/refund/"+liveId, url.Values{})
// 	assert.NotNil(t, res)
// }
//
// func TestAttendances_transfer(t *testing.T) {
// 	c := NewClient()
// 	c.admin = true
// 	res := c.get("attendances/transfer", url.Values{})
// 	assert.NotNil(t, res)
// }

// func TestAttendances_sendRedPacket(t *testing.T) {
// 	c := NewClient()
// 	c.admin = true
// 	res := c.get("attendances/sendRedPacket", url.Values{})
// 	assert.NotNil(t, res)
// }
