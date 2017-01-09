package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestApps_create(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.postData("apps", url.Values{})
	assert.NotNil(t, res)
}

func createApp(c *Client) string {
	res := c.postData("apps", url.Values{})
	return toStr(res.Get("appId").MustInt())
}

func TestApps_update(t *testing.T) {
	c, _ := NewClientAndUser()
	appIdStr := createApp(c)
	res := c.postData("apps/"+appIdStr, url.Values{"name": {"微信小程序"},
		"qrcodeKey": {"abc"}, "desc": {"商量"}, "iconKey": {"cdf"}})
	assert.NotNil(t, res.Interface())
}

func TestApps_updateImg(t *testing.T) {
	c, _ := NewClientAndUser()
	appIdStr := createApp(c)
	res := c.postData("apps/"+appIdStr+"/imgs", url.Values{"op": {"add"}, "imgKey": {"abc"}})
	assert.NotNil(t, res.Interface())
}

func TestApps_mylist(t *testing.T) {
	c, _ := NewClientAndUser()
	createApp(c)
	res := c.getArrayData("apps/mylist", url.Values{})
	assert.True(t, len(res.MustArray()) > 0)
}

func TestApps_one(t *testing.T) {
	c, _ := NewClientAndUser()
	appId := createApp(c)
	res := c.getData("apps/"+appId, url.Values{})
	assert.NotNil(t, res.Interface())
}
