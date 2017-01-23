package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

//
// func TestTopics_create(t *testing.T) {
// 	c := NewClient()
// 	res := c.postData("topics", url.Values{"name": {"创业"}})
// 	assert.NotNil(t, res.Interface())
// }

func TestTopics_getList(t *testing.T) {
	c := NewClient()
	res := c.getArrayData("topics", url.Values{})
	assert.NotNil(t, res.MustArray())
}

func getTopic(c *Client) string {
	res := c.getArrayData("topics", url.Values{})
	topic := res.GetIndex(0)
	return toStr(topic.Get("topicId").MustInt())
}

func TestTopics_init(t *testing.T) {
	c := NewClient()
	res := c.getData("topics/init", url.Values{})
	assert.NotNil(t, res.Interface())
}
