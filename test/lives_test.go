package liveserver

import (
	"net/url"
	"testing"

	"time"

	"github.com/bitly/go-simplejson"
	"github.com/stretchr/testify/assert"
)

func lastPrepareLive(c *Client) string {
	res := c.getData("lives/lastPrepare", url.Values{})
	liveId := toStr(res.Get("liveId").MustInt())
	return liveId
}

func createLiveWithAmount(c *Client, amount int) string {
	res := c.postData("lives", url.Values{})
	liveId := toStr(res.Get("liveId").MustInt())
	updateLiveAndSubmitThenPublish(c, liveId, amount, 1)
	return liveId
}

func createLiveNotNeedPay(c *Client) string {
	res := c.postData("lives", url.Values{})
	liveId := toStr(res.Get("liveId").MustInt())
	updateLiveAndSubmitThenPublish(c, liveId, 100, 0)
	return liveId
}

func createLive(c *Client) string {
	return createLiveWithAmount(c, 100)
}

func beginLive(c *Client, liveId string) {
	c.getData("lives/"+liveId+"/begin", url.Values{})
}

func updateLiveAndSubmitThenPublish(c *Client, liveId string, amount int, needPay int) {
	planTs := time.Now().Add(1 * time.Hour).Format("2006-01-02 15:04:05")
	c.postData("lives/"+liveId, url.Values{"subject": {"C++ 编程"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"amount":   {toStr(amount)}, "detail": {"我是周子敬，以太资本创始人兼  CEO 。曾任华兴资本副总裁，领导完成多个融资项目，包括美乐乐，途家，猎聘，唱吧，友盟，中清龙图，有缘网等。曾任阿里巴巴资深产品经理，并有过网游公司的创业经验。2014  年创立以太资本，至今，已为超过 350 个项目完成融资，融资总额逾 13 亿美元。成功案例包括知乎，映客，河狸家，蘑菇街，铜板街，达达，小猪短租等。作为资深天使投资人，曾投资今日头条、Loho、团车网以及小麦公社等著名企业。2015 年 11 月，《财富》（中文版）评选中国 40 位 40 岁以下的商界精英，名列第 33 位。本次 Live 我将就创业者最关注的一些融资问题进行解答，希望能帮助到正走在创业道路上的伙伴们。"},
		"speakerIntro": {"我是悟空，热爱旅行，小众目的地爱好者，也是人文地理摄影师。「摆脱千篇一律的旅程，探索完全属于你自己的世界，去尝试遇见全新的事物，直到世界成为你生命里的一部分。」这是我的旅行哲学，是我读世界的方式。幸运的是至今我也如此实践着，设计飞机涂装，为联合国拍摄公益项目，在远东的堪察加半岛住上一段，去拉达克隐秘的赞斯卡山谷找一座悬崖上的寺庙，为了看一眼 K2 在巴基斯坦喀喇昆仑山区徒步两个星期……每段旅程对我来说都是全新的世界。"},
		"planTs":       {planTs},
		"needPay":      {toStr(needPay)}})
	c.getData("lives/"+liveId+"/submitReview", url.Values{})
	c.admin = true
	c.getData("lives/"+liveId+"/publish", url.Values{})
}

func TestLives_get(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	live := c.getData("lives/"+liveId, url.Values{})
	assert.NotNil(t, live)
	assert.NotNil(t, live.Get("rtmpUrl").Interface())
	assert.NotNil(t, live.Get("pushUrl").Interface())
	assert.NotNil(t, live.Get("foreignPushUrl").Interface())
	assert.NotNil(t, live.Get("hlsUrls").MustArray())
	assert.NotNil(t, live.Get("hlsUrl").Interface())
	assert.NotNil(t, live.Get("flvUrl").Interface())
	assert.NotNil(t, live.Get("videoUrl").Interface())
	assert.NotNil(t, live.Get("rtmpKey").Interface())
	assert.NotNil(t, live.Get("status").Interface())
	assert.NotNil(t, live.Get("liveId").Interface())
	assert.NotNil(t, live.Get("endTs").Interface())
	assert.NotNil(t, live.Get("beginTs").Interface())
	assert.NotNil(t, live.Get("subject").Interface())
	assert.NotNil(t, live.Get("canJoin").Interface())
	assert.NotNil(t, live.Get("planTs").Interface())

	assert.NotNil(t, live.Get("amount").Interface())
	assert.NotNil(t, live.Get("realAmount").Interface())

	assert.NotNil(t, live.Get("attendanceCount").Interface())
	assert.NotNil(t, live.Get("owner").Interface())
}

func TestLives_getPublic(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	live := c2.getData("lives/"+liveId, url.Values{})

	_, exists := live.CheckGet("rtmpUrl")
	assert.False(t, exists)
	_, exists = live.CheckGet("rtmpKey")
	assert.False(t, exists)
	assert.False(t, live.Get("canJoin").MustBool())
}

func getLive(c *Client, liveId string) *simplejson.Json {
	live := c.getData("lives/"+liveId, url.Values{})
	return live
}

func TestLives_livings(t *testing.T) {
	c := NewClient()
	res := c.get("lives/on", url.Values{})
	assert.NotNil(t, res)
}

func TestLives_alive(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	beginLive(c, liveId)
	res := c.getData("lives/"+liveId+"/alive", url.Values{})
	assert.NotNil(t, res)
}

func TestLives_end(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	beginLive(c, liveId)
	res := c.getData("lives/"+liveId+"/end", url.Values{})
	assert.NotNil(t, res)
}

func endLive(c *Client, liveId string) {
	c.getData("lives/"+liveId+"/end", url.Values{})
}

func finishLive(c *Client, liveId string) {
	c.getData("lives/"+liveId+"/finish", url.Values{})
}

func beginAndFinshLive(c *Client, liveId string) {
	beginLive(c, liveId)
	endLive(c, liveId)
	finishLive(c, liveId)
}

func TestLives_finish(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	beginLive(c, liveId)
	endLive(c, liveId)
	res := c.get("lives/"+liveId+"/finish", url.Values{})
	assert.NotNil(t, res.Interface())
}

func TestLives_end_error(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.get("lives/"+liveId+"/end", url.Values{})
	assert.Equal(t, res.Get("status").MustString(), "live_not_start")
}

func TestLives_update(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := lastPrepareLive(c)
	planTs := time.Now().Add(1 * time.Hour).Format("2006-01-02 15:04:05")
	res := c.postData("lives/"+liveId, url.Values{"subject": {"C++ 编程"},
		"coverUrl":      {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"coursewareUrl": {"http://i.quzhiboapp.com/jMyBvR.pdf"},
		"amount":        {"30000"}, "detail": {"我是周子敬，以太资本创始人兼  CEO 。曾任华兴资本副总裁，领导完成多个融资项目，包括美乐乐，途家，猎聘，唱吧，友盟，中清龙图，有缘网等。曾任阿里巴巴资深产品经理，并有过网游公司的创业经验。2014  年创立以太资本，至今，已为超过 350 个项目完成融资，融资总额逾 13 亿美元。成功案例包括知乎，映客，河狸家，蘑菇街，铜板街，达达，小猪短租等。作为资深天使投资人，曾投资今日头条、Loho、团车网以及小麦公社等著名企业。2015 年 11 月，《财富》（中文版）评选中国 40 位 40 岁以下的商界精英，名列第 33 位。本次 Live 我将就创业者最关注的一些融资问题进行解答，希望能帮助到正走在创业道路上的伙伴们。"},
		"speakerIntro": {"我是悟空，热爱旅行，小众目的地爱好者，也是人文地理摄影师。「摆脱千篇一律的旅程，探索完全属于你自己的世界，去尝试遇见全新的事物，直到世界成为你生命里的一部分。」这是我的旅行哲学，是我读世界的方式。幸运的是至今我也如此实践着，设计飞机涂装，为联合国拍摄公益项目，在远东的堪察加半岛住上一段，去拉达克隐秘的赞斯卡山谷找一座悬崖上的寺庙，为了看一眼 K2 在巴基斯坦喀喇昆仑山区徒步两个星期……每段旅程对我来说都是全新的世界。"},
		"planTs":       {planTs}, "previewUrl": {"http://video.quzhiboapp.com/vUh9YBTr.mp4"}, "needPay": {"1"},
		"notice":    {"主播微信是 lzwjava"},
		"shareIcon": {"1"}})
	assert.NotNil(t, res.Interface())
	res = getLive(c, liveId)
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("coverUrl").Interface())
	assert.NotNil(t, res.Get("previewUrl").Interface())
	assert.Equal(t, res.Get("detail").MustString(), "我是周子敬，以太资本创始人兼  CEO 。曾任华兴资本副总裁，领导完成多个融资项目，包括美乐乐，途家，猎聘，唱吧，友盟，中清龙图，有缘网等。曾任阿里巴巴资深产品经理，并有过网游公司的创业经验。2014  年创立以太资本，至今，已为超过 350 个项目完成融资，融资总额逾 13 亿美元。成功案例包括知乎，映客，河狸家，蘑菇街，铜板街，达达，小猪短租等。作为资深天使投资人，曾投资今日头条、Loho、团车网以及小麦公社等著名企业。2015 年 11 月，《财富》（中文版）评选中国 40 位 40 岁以下的商界精英，名列第 33 位。本次 Live 我将就创业者最关注的一些融资问题进行解答，希望能帮助到正走在创业道路上的伙伴们。")
	assert.Equal(t, res.Get("speakerIntro").MustString(), "我是悟空，热爱旅行，小众目的地爱好者，也是人文地理摄影师。「摆脱千篇一律的旅程，探索完全属于你自己的世界，去尝试遇见全新的事物，直到世界成为你生命里的一部分。」这是我的旅行哲学，是我读世界的方式。幸运的是至今我也如此实践着，设计飞机涂装，为联合国拍摄公益项目，在远东的堪察加半岛住上一段，去拉达克隐秘的赞斯卡山谷找一座悬崖上的寺庙，为了看一眼 K2 在巴基斯坦喀喇昆仑山区徒步两个星期……每段旅程对我来说都是全新的世界。")
	assert.Equal(t, res.Get("coverUrl").MustString(), "http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg")
	assert.Equal(t, "http://i.quzhiboapp.com/jMyBvR.pdf", res.Get("coursewareUrl").MustString())
	assert.Equal(t, res.Get("planTs").MustString(), planTs)
	assert.Equal(t, res.Get("amount").MustInt(), 30000)
	assert.Equal(t, res.Get("needPay").MustInt(), 1)
	assert.Equal(t, res.Get("shareIcon").MustInt(), 1)
	assert.NotNil(t, res.Get("topic"))
	assert.Equal(t, res.Get("notice").MustString(), "主播微信是 lzwjava")
}

func TestLives_begin(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.getData("lives/"+liveId+"/begin", url.Values{})
	assert.True(t, res.MustBool())
}

func TestLives_begin_wait(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.getData("lives/"+liveId+"/begin", url.Values{})
	res = c.getData("lives/"+liveId+"/wait", url.Values{})
	res = c.getData("lives/"+liveId, url.Values{})
	assert.Equal(t, res.Get("status").MustInt(), 10)
}

func TestLives_begin_error(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.postData("lives", url.Values{})
	liveId := toStr(res.Get("liveId").MustInt())
	beginRes := c.get("lives/"+liveId+"/begin", url.Values{})
	assert.Equal(t, beginRes.Get("status").MustString(), "live_not_wait")
}

func TestLives_setReview(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.getData("lives/"+liveId+"/setReview", url.Values{})
	assert.NotNil(t, res.Interface())
	res = c.getData("lives/"+liveId, url.Values{})
	assert.Equal(t, res.Get("status").MustInt(), 5)
}

func TestLives_submitReview(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	liveId := lastPrepareLive(c)
	planTs := time.Now().Add(1 * time.Hour).Format("2006-01-02 15:04:05")
	updateRes := c.postData("lives/"+liveId, url.Values{"subject": {"C++ 编程"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"amount":   {"30000"}, "detail": {"我是周子敬，以太资本创始人兼  CEO 。曾任华兴资本副总裁，领导完成多个融资项目，包括美乐乐，途家，猎聘，唱吧，友盟，中清龙图，有缘网等。曾任阿里巴巴资深产品经理，并有过网游公司的创业经验。2014  年创立以太资本，至今，已为超过 350 个项目完成融资，融资总额逾 13 亿美元。成功案例包括知乎，映客，河狸家，蘑菇街，铜板街，达达，小猪短租等。作为资深天使投资人，曾投资今日头条、Loho、团车网以及小麦公社等著名企业。2015 年 11 月，《财富》（中文版）评选中国 40 位 40 岁以下的商界精英，名列第 33 位。本次 Live 我将就创业者最关注的一些融资问题进行解答，希望能帮助到正走在创业道路上的伙伴们。"},
		"speakerIntro": {"我是悟空，热爱旅行，小众目的地爱好者，也是人文地理摄影师。「摆脱千篇一律的旅程，探索完全属于你自己的世界，去尝试遇见全新的事物，直到世界成为你生命里的一部分。」这是我的旅行哲学，是我读世界的方式。幸运的是至今我也如此实践着，设计飞机涂装，为联合国拍摄公益项目，在远东的堪察加半岛住上一段，去拉达克隐秘的赞斯卡山谷找一座悬崖上的寺庙，为了看一眼 K2 在巴基斯坦喀喇昆仑山区徒步两个星期……每段旅程对我来说都是全新的世界。"},
		"notice":       {"主播微信是 lzwjava"},
		"planTs":       {planTs}})
	assert.NotNil(t, updateRes)
	time.Sleep(time.Second)
	res := c.getData("lives/"+liveId+"/submitReview", url.Values{})
	assert.True(t, res.MustBool())

	res = c.get("lives/"+liveId+"/submitReview", url.Values{})
	assert.Equal(t, res.Get("status").MustString(), "already_review")
}

func TestLives_publish(t *testing.T) {
	c, _ := NewClientAndUser()
	createLive(c)
}

func TestLives_lastPrepare(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.getData("lives/lastPrepare", url.Values{})
	assert.NotNil(t, res)
	liveId := res.Get("liveId").MustInt()
	assert.NotNil(t, liveId)
	res = c.getData("lives/lastPrepare", url.Values{})
	newLiveId := res.Get("liveId").MustInt()
	assert.Equal(t, liveId, newLiveId)
}

func TestLives_my(t *testing.T) {
	c, _ := NewClientAndUser()
	createLive(c)

	res := c.getData("lives/me", url.Values{})
	assert.Equal(t, len(res.MustArray()), 1)
}

func TestLives_attended(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	createAttendance(c2, liveId)

	res := c2.getData("lives/attended", url.Values{})
	assert.Equal(t, len(res.MustArray()), 1)
}

func createLiveAndAttendance() (*Client, *Client, string, string) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, u2 := NewClientAndUser()
	createAttendance(c2, liveId)
	return c, c2, u2, liveId
}

func createLiveAndWeChatAttendance() (*Client, *Client, string, string) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	deleteSnsUser()
	c2, u2 := NewClientAndUser()
	insertSnsUser(u2)
	createWechatAttendance(c2, u2, liveId)
	return c, c2, u2, liveId
}

func createLiveAndWeChatAppAttendance() (*Client, *Client, string, string) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	deleteAppSnsUser()
	c2, u2 := NewClientAndWeChatUser()
	insertAppSnsUser(u2)
	createWechatAppAttendance(c2, u2, liveId)
	return c, c2, u2, liveId
}

func TestLives_attendedUsers(t *testing.T) {
	_, c2, _, liveId := createLiveAndAttendance()
	res := c2.getData("lives/"+liveId+"/users", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, len(res.MustArray()), 1)
}

func TestLives_notifyLiveStart(t *testing.T) {
	c, _, _, liveId := createLiveAndWeChatAttendance()
	res := c.getData("lives/"+liveId+"/notify", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, res.Get("succeedCount").MustInt(), res.Get("total").MustInt())
}

func TestLives_notifyLiveStart_WeChatApp(t *testing.T) {
	c, _, _, liveId := createLiveAndWeChatAppAttendance()
	res := c.getData("lives/"+liveId+"/notify", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, res.Get("succeedCount").MustInt(), res.Get("total").MustInt())
}

func TestLives_notifyLiveStart_oneHour(t *testing.T) {
	c, _, _, liveId := createLiveAndWeChatAttendance()
	res := c.getData("lives/"+liveId+"/notify", url.Values{"oneHour": {"1"}})
	assert.NotNil(t, res)
	assert.Equal(t, res.Get("succeedCount").MustInt(), res.Get("total").MustInt())
}

func TestLives_fixAttedanceCount(t *testing.T) {
	c := NewClient()
	res := c.getData("lives/fixAttendanceCount", url.Values{})
	assert.NotNil(t, res)
}

func TestLives_create(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.postData("lives", url.Values{})
	assert.NotNil(t, res)
}

func TestLives_groupSend(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.getData("lives/"+liveId+"/groupSend", url.Values{})
	assert.NotNil(t, res)
}

func TestLives_notifyVideo(t *testing.T) {
	c, _, _, liveId := createLiveAndWeChatAttendance()
	res := c.getData("lives/"+liveId+"/notifyVideo", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, res.Get("succeedCount").MustInt(), res.Get("total").MustInt())
}

func TestLives_import(t *testing.T) {
	deleteTable("coupons", true)
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	c.admin = true
	res := c.getData("lives/"+liveId+"/import", url.Values{})
	assert.NotNil(t, res.Interface())
}

func TestLives_recommend(t *testing.T) {
	c := NewClient()
	res := c.get("lives/recommend", url.Values{"skipLiveId": {"10"}})
	assert.NotNil(t, res)
}

func TestLives_error(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	liveId := createLive(c)
	res := c.getData("lives/"+liveId+"/error", url.Values{})
	assert.NotNil(t, res)
	live := getLive(c, liveId)
	assert.Equal(t, live.Get("status").MustInt(), 35)
}

func TestLives_notifyLiveStartRelated(t *testing.T) {
	_, _, _, liveId2 := createLiveAndWeChatAttendance()

	c, _, _, liveId := createLiveAndAttendance()
	res := c.getData("lives/"+liveId+"/notifyRelated", url.Values{"relatedLiveId": {liveId2}})
	assert.NotNil(t, res)
	assert.Equal(t, res.Get("succeedCount").MustInt(), res.Get("total").MustInt())
}

func TestLives_updateTopic(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	topicId := getTopic(c)
	res := c.postData("lives/"+liveId+"/topic", url.Values{"op": {"add"}, "topicId": {topicId}})
	assert.NotNil(t, res.Interface())
	live := getLive(c, liveId)
	assert.Equal(t, toStr(live.Get("topic").Get("topicId").MustInt()), topicId)
}

func TestLives_delTopic(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.postData("lives/"+liveId+"/topic", url.Values{"op": {"del"}})
	assert.NotNil(t, res.Interface())
}

func TestLives_notifyNewLive(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	subscribeWechat(c)
	c.postData("self", url.Values{"liveSubscribe": {"1"}})

	c2, _ := NewClientAndUser()
	liveId := createLive(c2)
	res := c2.getData("lives/"+liveId+"/notifyNewLive", url.Values{})
	assert.NotNil(t, res.Interface())
}
