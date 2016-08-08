package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestLives_create(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.postData("lives", url.Values{"subject": {"直播啦"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/3.pic_hd.jpg"}, "amount": {"5000"}})
	assert.NotNil(t, res)
	liveId := toStr(res.Get("id").MustInt())
	assert.NotNil(t, liveId)
}

func createLive(c *Client) string {
	res := c.postData("lives", url.Values{"subject": {"直播啦"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/3.pic_hd.jpg"}, "amount": {"5000"}})
	liveId := toStr(res.Get("id").MustInt())
	return liveId
}

func TestLives_get(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	live := c.getData("lives/"+liveId, url.Values{})
	assert.NotNil(t, live)
	assert.NotNil(t, live.Get("rtmpUrl"))
	assert.NotNil(t, live.Get("status"))
	assert.NotNil(t, live.Get("key"))
	assert.NotNil(t, live.Get("id"))
	assert.NotNil(t, live.Get("end_ts"))
	assert.NotNil(t, live.Get("begin_ts"))
	assert.NotNil(t, live.Get("subject"))
}

func TestLives_livings(t *testing.T) {
	c := NewClient()
	res := c.get("lives/on", url.Values{})
	assert.NotNil(t, res)
}

func TestLives_alive(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.getData("lives/"+liveId+"/alive", url.Values{})
	assert.NotNil(t, res)
}

func TestLives_end(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.getData("lives/"+liveId+"/end", url.Values{})
	assert.NotNil(t, res)
}

func TestLives_update(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.postData("lives/"+liveId, url.Values{"subject": {"C++ 编程"},
		"coverUrl": {"http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg"},
		"amount":   {"30000"}, "detail": {"这次主要讲下多年来 C++ 的编程实战"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("coverUrl"))
	assert.Equal(t, res.Get("detail").MustString(), "这次主要讲下多年来 C++ 的编程实战")
	assert.Equal(t, res.Get("coverUrl").MustString(), "http://obcbndtjd.bkt.clouddn.com/2.pic_hd.jpg")
	assert.NotNil(t, res.Get("amount").MustInt(), 30000)
}
