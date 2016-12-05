package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestLiveViews_create(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.postData("liveViews", url.Values{"liveId": {liveId}, "platform": {"wechat"}, "liveStatus": {"20"}})
	assert.NotNil(t, res.Get("liveViewId").MustInt())
}

func createLiveView(c *Client, liveId string) string {
	res := c.postData("liveViews", url.Values{"liveId": {liveId}, "platform": {"wechat"}, "liveStatus": {"20"}})
	return toStr(res.Get("liveViewId").MustInt())
}

func TestLiveViews_end(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	liveViewId := createLiveView(c, liveId)

	res := c.getData("liveViews/"+liveViewId+"/end", url.Values{})
	assert.NotNil(t, res)
}
