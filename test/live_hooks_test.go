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
	newLive := getLive(c, liveId)
	assert.Equal(t, newLive.Get("status").MustInt(), 20)
}

func TestLiveHooks_onUnPublish(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	live := getLive(c, liveId)
	res := c.postWithParams("liveHooks/onUnPublish", url.Values{
		"stream": {live.Get("rtmpKey").MustString()},
		"action": {"on_unpublish"}})
	assert.NotNil(t, res)
	newLive := getLive(c, liveId)
	assert.Equal(t, newLive.Get("status").MustInt(), 25)
}

func publishStream(c *Client, liveId string) {
	live := getLive(c, liveId)
	c.postWithParams("liveHooks/onPublish", url.Values{
		"stream": {live.Get("rtmpKey").MustString()},
		"action": {"on_publish"}})
}

func unPublishStream(c *Client, liveId string) {
	live := getLive(c, liveId)
	c.postWithParams("liveHooks/onUnPublish", url.Values{
		"stream": {live.Get("rtmpKey").MustString()},
		"action": {"on_unpublish"}})
}

func TestLiveHooks_resumeLive(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	publishStream(c, liveId)
	unPublishStream(c, liveId)
	publishStream(c, liveId)
}