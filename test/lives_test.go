package liveserver

import (
	"net/url"
	"testing"

	"time"

	"github.com/bitly/go-simplejson"
	"github.com/stretchr/testify/assert"
)

func TestLives_create(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.postData("lives", url.Values{"subject": {"直播啦"}})
	assert.NotNil(t, res)
	liveId := toStr(res.Get("liveId").MustInt())
	assert.NotNil(t, liveId)
}

func createSimpleLive(c *Client) string {
	res := c.postData("lives", url.Values{"subject": {"直播啦"}})
	liveId := toStr(res.Get("liveId").MustInt())
	return liveId
}

func createLive(c *Client) string {
	liveId := createSimpleLive(c)
	updateLiveAndPublish(c, liveId)
	return liveId
}

func beginLive(c *Client, liveId string) {
	c.getData("lives/"+liveId+"/begin", url.Values{})
}

func updateLiveAndPublish(c *Client, liveId string) {
	planTs := time.Now().Add(1 * time.Hour).Format("2006-01-02 15:04:05")
	c.postData("lives/"+liveId, url.Values{"subject": {"C++ 编程"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"amount":   {"100"}, "detail": {"这次主要讲下多年来 C++ 的编程实战"},
		"planTs": {planTs}})
	c.getData("lives/"+liveId+"/publish", url.Values{})
}

func TestLives_get(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	live := c.getData("lives/"+liveId, url.Values{})
	assert.NotNil(t, live)
	assert.NotNil(t, live.Get("rtmpUrl").Interface())
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

func TestLives_update(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createSimpleLive(c)
	planTs := time.Now().Add(1 * time.Hour).Format("2006-01-02 15:04:05")
	res := c.postData("lives/"+liveId, url.Values{"subject": {"C++ 编程"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"amount":   {"30000"}, "detail": {"这次主要讲下多年来 C++ 的编程实战"},
		"planTs": {planTs}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("coverUrl"))
	assert.Equal(t, res.Get("detail").MustString(), "这次主要讲下多年来 C++ 的编程实战")
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

func TestLives_publish(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createSimpleLive(c)
	planTs := time.Now().Add(1 * time.Hour).Format("2006-01-02 15:04:05")
	updateRes := c.postData("lives/"+liveId, url.Values{"subject": {"C++ 编程"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"amount":   {"30000"}, "detail": {"这次主要讲下多年来 C++ 的编程实战"},
		"planTs": {planTs}})
	assert.NotNil(t, updateRes)
	time.Sleep(time.Second)
	res := c.getData("lives/"+liveId+"/publish", url.Values{})
	assert.True(t, res.MustBool())
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
