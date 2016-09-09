package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestStates_create(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	res := c.postData("states", url.Values{"liveId": {liveId}})
	assert.NotNil(t, res.Get("hash").Interface())
}

func createState(c *Client, liveId string) string {
	res := c.postData("states", url.Values{"liveId": {liveId}})
	return res.Get("hash").MustString()
}
