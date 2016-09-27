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

func createLive(c *Client) string {
	res := c.postData("lives", url.Values{})
	liveId := toStr(res.Get("liveId").MustInt())
	updateLiveAndSubmitThenPublish(c, liveId)
	return liveId
}

func beginLive(c *Client, liveId string) {
	c.getData("lives/"+liveId+"/begin", url.Values{})
}

func updateLiveAndSubmitThenPublish(c *Client, liveId string) {
	planTs := time.Now().Add(1 * time.Hour).Format("2006-01-02 15:04:05")
	c.postData("lives/"+liveId, url.Values{"subject": {"C++ 编程"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"amount":   {"100"}, "detail": {"我是周子敬，以太资本创始人兼  CEO 。曾任华兴资本副总裁，领导完成多个融资项目，包括美乐乐，途家，猎聘，唱吧，友盟，中清龙图，有缘网等。曾任阿里巴巴资深产品经理，并有过网游公司的创业经验。2014  年创立以太资本，至今，已为超过 350 个项目完成融资，融资总额逾 13 亿美元。成功案例包括知乎，映客，河狸家，蘑菇街，铜板街，达达，小猪短租等。作为资深天使投资人，曾投资今日头条、Loho、团车网以及小麦公社等著名企业。2015 年 11 月，《财富》（中文版）评选中国 40 位 40 岁以下的商界精英，名列第 33 位。本次 Live 我将就创业者最关注的一些融资问题进行解答，希望能帮助到正走在创业道路上的伙伴们。"},
		"planTs": {planTs}})
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
	assert.NotNil(t, live.Get("hlsUrl").Interface())
	assert.NotNil(t, live.Get("flvUrl").Interface())
	assert.NotNil(t, live.Get("rtmpKey").Interface())
	assert.NotNil(t, live.Get("status").Interface())
	assert.NotNil(t, live.Get("liveId").Interface())
	assert.NotNil(t, live.Get("endTs").Interface())
	assert.NotNil(t, live.Get("beginTs").Interface())
	assert.NotNil(t, live.Get("subject").Interface())
	assert.NotNil(t, live.Get("canJoin").Interface())
	assert.NotNil(t, live.Get("planTs").Interface())

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
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"amount":   {"30000"}, "detail": {"我是周子敬，以太资本创始人兼  CEO 。曾任华兴资本副总裁，领导完成多个融资项目，包括美乐乐，途家，猎聘，唱吧，友盟，中清龙图，有缘网等。曾任阿里巴巴资深产品经理，并有过网游公司的创业经验。2014  年创立以太资本，至今，已为超过 350 个项目完成融资，融资总额逾 13 亿美元。成功案例包括知乎，映客，河狸家，蘑菇街，铜板街，达达，小猪短租等。作为资深天使投资人，曾投资今日头条、Loho、团车网以及小麦公社等著名企业。2015 年 11 月，《财富》（中文版）评选中国 40 位 40 岁以下的商界精英，名列第 33 位。本次 Live 我将就创业者最关注的一些融资问题进行解答，希望能帮助到正走在创业道路上的伙伴们。"},
		"planTs": {planTs}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("coverUrl"))
	assert.Equal(t, res.Get("detail").MustString(), "我是周子敬，以太资本创始人兼  CEO 。曾任华兴资本副总裁，领导完成多个融资项目，包括美乐乐，途家，猎聘，唱吧，友盟，中清龙图，有缘网等。曾任阿里巴巴资深产品经理，并有过网游公司的创业经验。2014  年创立以太资本，至今，已为超过 350 个项目完成融资，融资总额逾 13 亿美元。成功案例包括知乎，映客，河狸家，蘑菇街，铜板街，达达，小猪短租等。作为资深天使投资人，曾投资今日头条、Loho、团车网以及小麦公社等著名企业。2015 年 11 月，《财富》（中文版）评选中国 40 位 40 岁以下的商界精英，名列第 33 位。本次 Live 我将就创业者最关注的一些融资问题进行解答，希望能帮助到正走在创业道路上的伙伴们。")
	assert.Equal(t, res.Get("coverUrl").MustString(), "http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg")
	assert.Equal(t, res.Get("planTs").MustString(), planTs)
	assert.NotNil(t, res.Get("amount").MustInt(), 30000)
}

func TestLives_begin(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.getData("lives/"+liveId+"/begin", url.Values{})
	assert.True(t, res.MustBool())
}

func TestLives_begin_error(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.postData("lives", url.Values{})
	liveId := toStr(res.Get("liveId").MustInt())
	beginRes := c.get("lives/"+liveId+"/begin", url.Values{})
	assert.Equal(t, beginRes.Get("status").MustString(), "live_not_wait")
}

func TestLives_submitReview(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := lastPrepareLive(c)
	planTs := time.Now().Add(1 * time.Hour).Format("2006-01-02 15:04:05")
	updateRes := c.postData("lives/"+liveId, url.Values{"subject": {"C++ 编程"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"amount":   {"30000"}, "detail": {"我是周子敬，以太资本创始人兼  CEO 。曾任华兴资本副总裁，领导完成多个融资项目，包括美乐乐，途家，猎聘，唱吧，友盟，中清龙图，有缘网等。曾任阿里巴巴资深产品经理，并有过网游公司的创业经验。2014  年创立以太资本，至今，已为超过 350 个项目完成融资，融资总额逾 13 亿美元。成功案例包括知乎，映客，河狸家，蘑菇街，铜板街，达达，小猪短租等。作为资深天使投资人，曾投资今日头条、Loho、团车网以及小麦公社等著名企业。2015 年 11 月，《财富》（中文版）评选中国 40 位 40 岁以下的商界精英，名列第 33 位。本次 Live 我将就创业者最关注的一些融资问题进行解答，希望能帮助到正走在创业道路上的伙伴们。"},
		"planTs": {planTs}})
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

func TestLives_attendedUsers(t *testing.T) {
	_, c2, _, liveId := createLiveAndAttendance()
	res := c2.getData("lives/"+liveId+"/users", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, len(res.MustArray()), 1)
}

func TestLives_notify(t *testing.T) {
	c, _, _, liveId := createLiveAndAttendance()
	res := c.getData("lives/"+liveId+"/notify", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, res.Get("succeedCount").MustInt(), res.Get("total").MustInt())
}

func TestLives_notifyOneUser(t *testing.T) {
	c, _, userId, liveId := createLiveAndAttendance()
	res := c.getData("lives/"+liveId+"/notifyOneUser", url.Values{"userId": {userId}})
	assert.NotNil(t, res)
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
