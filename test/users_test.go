package liveserver

import (
	_ "fmt"
	"net/url"
	_ "reflect"
	"testing"
	"time"

	"github.com/stretchr/testify/assert"
)

func TestUser_RegisterAndLogin(t *testing.T) {
	c := NewClient()
	name := randomString()
	mobile := randomMobile()
	res := c.postData("users", url.Values{"mobilePhoneNumber": {mobile},
		"username": {name}, "smsCode": {"5555"}, "avatarUrl": {"http://i.quzhiboapp.com/defaultAvatar1.png"}})
	assert.Equal(t, name, res.Get("username").MustString())
	assert.NotNil(t, res.Get("userId"))
	assert.NotNil(t, res.Get("created"))
	assert.NotNil(t, res.Get("updated"))

	res = c.postData("login", url.Values{"mobilePhoneNumber": {mobile},
		"smsCode": {"5555"}})
	assert.Equal(t, name, res.Get("username").MustString())
	assert.Equal(t, mobile, res.Get("mobilePhoneNumber").MustString())
	assert.Equal(t, "http://i.quzhiboapp.com/defaultAvatar1.png", res.Get("avatarUrl").MustString())
}

func TestUser_SpecialPhone(t *testing.T) {
	runSql("delete from users where mobilePhoneNumber='817015130624'", true)
	c := NewClient()
	name := randomString()
	res := c.postData("users", url.Values{"mobilePhoneNumber": {"817015130624"},
		"username": {name}, "smsCode": {"123456"}, "avatarUrl": {"http://i.quzhiboapp.com/defaultAvatar1.png"}})
	assert.NotNil(t, res.Get("username").Interface())
}

func TestUser_Update(t *testing.T) {
	c := NewClient()
	user := registerNewUser(c)
	updated := user.Get("updated").MustString()
	avatarUrl := "http://7xotd0.com1.z0.glb.clouddn.com/header_logo.png"

	time.Sleep(time.Second)

	newName := randomString()
	res := c.postData("self", url.Values{"username": {newName},
		"avatarUrl": {avatarUrl}})

	assert.Equal(t, newName, res.Get("username").MustString())
	assert.Equal(t, avatarUrl, res.Get("avatarUrl").MustString())
	assert.NotEqual(t, updated, res.Get("updated").MustString())

	// Same username
	res = c.postData("self", url.Values{"username": {newName}})
	assert.Equal(t, newName, res.Get("username").MustString())
}

func TestUser_Self(t *testing.T) {
	c := NewClient()
	user := registerUser(c)
	self := c.getData("self", url.Values{})
	assert.Equal(t, self.Get("userId").MustInt(), user.Get("userId").MustInt())
	assert.Equal(t, self.Get("username").MustString(), user.Get("username").MustString())
}

func TestUser_requestSmsCode(t *testing.T) {
	c := NewClient()
	res := c.post("requestSmsCode", url.Values{"mobilePhoneNumber": {"xx"}})
	assert.Equal(t, res.Get("status").MustString(), "sms_wrong")
}

func TestUser_requestSmsCode_SpecialPhone(t *testing.T) {
	c := NewClient()
	res := c.post("requestSmsCode", url.Values{"mobilePhoneNumber": {"817015130624"}})
	assert.Equal(t, res.Get("status").MustString(), "success")
}

func TestUser_isRegister(t *testing.T) {
	c := NewClient()
	mobile := randomMobile()
	res := c.getData("users/isRegister", url.Values{"mobilePhoneNumber": {mobile}})
	assert.False(t, res.MustBool())

	res = registerUserWithPhone(c, mobile, randomString())
	assert.NotNil(t, res)

	res = c.getData("users/isRegister", url.Values{"mobilePhoneNumber": {mobile}})
	assert.True(t, res.MustBool())
}

func TestUser_logout(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.getData("logout", url.Values{})
	assert.NotNil(t, res)
	self := c.get("self", url.Values{})
	assert.Equal(t, self.Get("status").MustString(), "not_in_session")
}

func TestUsers_get(t *testing.T) {
	c, userId := NewClientAndUser()
	res := c.getData("users/"+userId, url.Values{})
	assert.NotNil(t, res.Get("username").Interface())
}

func TestUsers_fixAvatarUrl(t *testing.T) {
	c := NewClient()
	c.admin = true
	res := c.getData("users/fixAvatarUrl", url.Values{})
	assert.NotNil(t, res)
}

func TestUsers_bindPhone(t *testing.T) {
	c, userId := NewClientAndUser()
	runSql("update users set mobilePhoneNumber = null where userId="+userId, false)
	mobile := randomMobile()
	res := c.postData("users/bindPhone", url.Values{"mobilePhoneNumber": {mobile}, "smsCode": {"5555"}})
	assert.NotNil(t, res.Interface())
	user := c.getData("self", url.Values{})
	assert.Equal(t, user.Get("mobilePhoneNumber").MustString(), mobile)
}

func TestUsers_getList(t *testing.T) {
	c, userId := NewClientAndUser()
	res := c.postData("users/list", url.Values{"userIds": {"[" + userId + "]"}})
	assert.NotNil(t, res.Interface())
	assert.Equal(t, len(res.MustArray()), 1)
}

func TestUsers_fixSystemId(t *testing.T) {
	c := NewClient()
	c.admin = true
	res := c.getData("users/fixSystemId", url.Values{})
	assert.NotNil(t, res.Interface())
}

func TestUsers_liveSubscribe(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	subscribeWechat(c)
	res := c.postData("self", url.Values{"liveSubscribe": {"1"}})
	assert.NotNil(t, res.Get("liveSubscribe").MustInt(), 1)
}

func TestLives_userTopic(t *testing.T) {
	c, _ := NewClientAndUser()
	liveId := createLive(c)
	topicId := getTopic(c)
	c.postData("lives/"+liveId+"/topic", url.Values{"op": {"add"}, "topicId": {topicId}})

	c2, userId2 := NewClientAndWeChatUserWithName()
	createWechatAttendance(c2, userId2, liveId)
	user := c2.getData("self", url.Values{})
	topic := c2.getData("users/userTopic", url.Values{"username": {user.Get("username").MustString()}})
	assert.NotNil(t, topic.Interface())
	assert.Equal(t, toStr(topic.Get("topicId").MustInt()), topicId)
}

func TestUsers_usersByUsername_get(t *testing.T) {
	c, _ := NewClientAndUser()
	c.admin = true
	res := c.getData("users/usersByUsername", url.Values{"username": {"系统"}})
	assert.NotNil(t, res.Interface())
	assert.Equal(t, 1, len(res.MustArray()))
}
