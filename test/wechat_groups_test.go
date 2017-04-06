package liveserver

import (
	"net/url"
	"testing"

	simplejson "github.com/bitly/go-simplejson"
	"github.com/stretchr/testify/assert"
)

func genGroupUserName() string {
	return "超级用户群" + randomString()
}

func TestWechatGroups_create(t *testing.T) {
	c := NewClient()
	c.admin = true
	res := c.postData("wechatGroups", url.Values{"qrcodeKey": {"group6.jpeg"}, "groupUserName": {genGroupUserName()}})
	assert.NotNil(t, res.Get("wechatGroupId").MustInt())
}

func createGroup(c *Client) string {
	c.admin = true
	groupUserName := genGroupUserName()
	c.postData("wechatGroups", url.Values{"qrcodeKey": {"group6.jpeg"}, "groupUserName": {
		groupUserName}})
	return groupUserName
}

func TestWechatGroups_one(t *testing.T) {
	c := NewClient()
	c.admin = true
	groupUserName := genGroupUserName()
	res := c.postData("wechatGroups", url.Values{"qrcodeKey": {"group6.jpeg"}, "groupUserName": {
		groupUserName}})
	assert.NotNil(t, res.Get("wechatGroupId").MustInt())
	group := c.getData("wechatGroups/one", url.Values{"groupUserName": {groupUserName}})
	assert.NotNil(t, group.Get("groupId"))
}

func getGroup(c *Client, groupUserName string) *simplejson.Json {
	group := c.getData("wechatGroups/one", url.Values{"groupUserName": {groupUserName}})
	return group
}

func TestWechatGroups_update(t *testing.T) {
	c := NewClient()
	groupUserName := createGroup(c)
	c.postData("wechatGroups/update", url.Values{"groupUserName": {groupUserName}, "memberCount": {"100"}})
	res := getGroup(c, groupUserName)
	assert.Equal(t, res.Get("memberCount").MustInt(), 100)
	assert.Equal(t, res.Get("used").MustInt(), 1)
}

func TestWechatGroups_current(t *testing.T) {
	c := NewClient()
	createGroup(c)
	currentGroup := c.getData("wechatGroups/current", url.Values{})
	assert.NotNil(t, currentGroup.Get("groupId"))
}

func TestWeChatGroups_list(t *testing.T) {
	c := NewClient()
	createGroup(c)
	groups := c.getData("wechatGroups", url.Values{})
	assert.NotNil(t, groups)
}
