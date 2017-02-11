package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

// func TestVideos_import(t *testing.T) {
// 	c := NewClient()
// 	c.admin = true
// 	res := c.get("videos/import", url.Values{})
// 	assert.NotNil(t, res.Interface())
// }

func TestVideos_getList(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	beginAndFinshLive(c, liveId)
	res := c.getData("lives/"+liveId+"/videos", url.Values{})
	assert.Equal(t, len(res.MustArray()), 1)
	video := res.GetIndex(0)
	assert.NotNil(t, video.Get("videoId").Interface())
	assert.NotNil(t, video.Get("liveId").Interface())
	assert.NotNil(t, video.Get("title").Interface())
	assert.NotNil(t, video.Get("fileName").Interface())
	assert.NotNil(t, video.Get("created").Interface())
	assert.NotNil(t, video.Get("updated").Interface())
	if video.Get("type").MustString() == "mp4" {
		assert.NotNil(t, video.Get("url").Interface())
	} else {
		assert.NotNil(t, video.Get("m3u8Url").Interface())
	}
}

func TestVideos_create(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	beginAndFinshLive(c, liveId)
	live := getLive(c, liveId)
	res := c.postData("lives/"+liveId+"/videos", url.Values{"fileName": {live.Get("rtmpKey").MustString() + "_1"},
		"title": {"直播1"}})
	assert.NotNil(t, res.Interface())
}

func TestVideos_mp4Ready(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	beginAndFinshLive(c, liveId)
	c.admin = true
	res := c.getData("videos/mp4Ready", url.Values{"liveId": {liveId}})
	assert.NotNil(t, res.Interface())
}
