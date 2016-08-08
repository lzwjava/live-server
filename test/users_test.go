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
	md5Str := md5password("123456")
	name := randomString()
	mobile := randomMobile()
	res := c.postData("users", url.Values{"mobilePhoneNumber": {mobile},
		"username": {name}, "smsCode": {"5555"}, "password": {md5Str}})
	assert.Equal(t, name, res.Get("username").MustString())
	assert.NotNil(t, res.Get("userId"))
	assert.NotNil(t, res.Get("created"))
	assert.NotNil(t, res.Get("updated"))

	res = c.postData("login", url.Values{"mobilePhoneNumber": {mobile},
		"password": {md5password("123456")}})
	assert.Equal(t, name, res.Get("username").MustString())
	assert.Equal(t, mobile, res.Get("mobilePhoneNumber").MustString())
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
