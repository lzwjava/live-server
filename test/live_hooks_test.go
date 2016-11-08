package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestLiveHooks_onPublish(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	live := getLive(c, liveId)
	res := c.postWithParams("liveHooks/onPublish", url.Values{
		"stream": {live.Get("rtmpKey").MustString()},
		"action": {"on_publish"}})
	assert.NotNil(t, res)
}

func TestLiveHooks_onUnPublish(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	live := getLive(c, liveId)
	res := c.postWithParams("liveHooks/onUnPublish", url.Values{
		"stream": {live.Get("rtmpKey").MustString()},
		"action": {"on_unpublish"}})
	assert.NotNil(t, res)
}

func TestLiveHooks_onDvr(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	live := getLive(c, liveId)
	rtmpkey := live.Get("rtmpKey").MustString()
	res := c.postWithParams("liveHooks/onDvr", url.Values{
		"stream": {rtmpkey},
		"action": {"on_dvr"},
		"file":   {"./objs/nginx/html/live/" + rtmpkey + ".1420254068776.flv"}})
	assert.NotNil(t, res)

	videos := c.getArrayData("lives/"+liveId+"/videos", url.Values{})
	video := videos.GetIndex(0)
	assert.Equal(t, video.Get("endTs").MustString(), "1420254068776")
}
