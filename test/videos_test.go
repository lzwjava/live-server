package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestVideos_convert(t *testing.T) {
	c, _ := NewClientAndUser()
	deleteTable("videos", false)
	liveId := createLive(c)
	genVideo(c, liveId, "GJDnouWr.1478744070441.flv")
	genVideo(c, liveId, "GJDnouWr.1478754070441.flv")

	res := c.getData("videos/convert", url.Values{"liveId": {liveId}})
	assert.NotNil(t, res.Interface())
}
