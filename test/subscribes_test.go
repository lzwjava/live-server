package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestSubscribes_create(t *testing.T) {
	c, _ := NewClientAndUser()
	topicId := getTopic(c)
	res := c.postData("subscribes", url.Values{"topicId": {topicId}})
	assert.NotNil(t, res.Interface())
}

func subscribeTopic(c *Client, topicId string) {
	c.postData("subscribes", url.Values{"topicId": {topicId}})
}

func TestSubscribes_del(t *testing.T) {
	c, _ := NewClientAndUser()
	topicId := getTopic(c)
	subscribeTopic(c, topicId)
	res := c.postData("subscribes/del", url.Values{"topicId": {topicId}})
	assert.NotNil(t, res.Interface())
}
