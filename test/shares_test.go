package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestShares_create(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	res := c2.postData("shares", url.Values{"shareTs": {"1475528321"}, "liveId": {liveId}, "channel": {"wechat_timeline"}})
	assert.NotNil(t, res)
}

func TestShares_create_duplicate(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)

	c2, _ := NewClientAndUser()
	res := c2.postData("shares", url.Values{"shareTs": {"1475528321"}, "liveId": {liveId}, "channel": {"wechat_timeline"}})
	res = c2.postData("shares", url.Values{"shareTs": {"1475528321"}, "liveId": {liveId}, "channel": {"wechat_timeline"}})
	assert.NotNil(t, res)
}

func TestShares_create_mutipleLives(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	liveId2 := createLive(c)

	c2, _ := NewClientAndUser()
	res := c2.postData("shares", url.Values{"shareTs": {"1475528321"}, "liveId": {liveId}, "channel": {"wechat_timeline"}})
	assert.NotNil(t, res)

	res2 := c2.postData("shares", url.Values{"shareTs": {"1475528321"}, "liveId": {liveId2}, "channel": {"wechat_timeline"}})
	assert.NotNil(t, res2)
}

func createShare(c *Client, liveId string) {
	c.postData("shares", url.Values{"shareTs": {"1475528321"}, "liveId": {liveId}, "channel": {"wechat_timeline"}})
}
