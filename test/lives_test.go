package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestLives_create(t *testing.T) {
	c := NewClient()
	res := c.postData("lives", url.Values{"subject": {"直播啦"}})
	assert.NotNil(t, res)
	liveId := toStr(res.Get("id").MustInt())
	assert.NotNil(t, liveId)
}

func createLive(c *Client) string {
	res := c.postData("lives", url.Values{"subject": {"直播啦"}})
	liveId := toStr(res.Get("id").MustInt())
	return liveId
}

func TestLives_get(t *testing.T) {
	c := NewClient()
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
	c := NewClient()
	liveId := createLive(c)
	res := c.getData("lives/"+liveId+"/alive", url.Values{})
	assert.NotNil(t, res)
}
