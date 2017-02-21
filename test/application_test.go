package liveserver

import (
	"net/url"
	"testing"

	simplejson "github.com/bitly/go-simplejson"
	"github.com/stretchr/testify/assert"
)

func TestApplications_create(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	c.postData("applications", url.Values{"name": {"李智维"}, "wechatAccount": {"lzwjava"},
		"socialAccount": {"GitHub@lzwjava, 微博@lzwjava"}, "introduction": {"21岁CEO，李智维，趣直播首席客服。知识直播平台，上线三个月用户1万。中学搞搞算法竞赛，大二休学到 LeanCloud 工作。大三联合创立了 Reviewcode.cn，现为趣直播运营狗、技术狗，搬砖中。"}})
	application := getMyAppliction(c)
	assert.Equal(t, application.Get("wechatAccount").MustString(), "lzwjava")
}

func TestApplications_create_wechatError(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	res := c.post("applications", url.Values{"name": {"李智维"}, "wechatAccount": {"牛逼啊"},
		"socialAccount": {"GitHub@lzwjava, 微博@lzwjava"}, "introduction": {"21岁CEO，李智维，趣直播首席客服。知识直播平台，上线三个月用户1万。中学搞搞算法竞赛，大二休学到 LeanCloud 工作。大三联合创立了 Reviewcode.cn，现为趣直播运营狗、技术狗，搬砖中。"}})
	assert.Equal(t, res.Get("status").MustString(), "wechat_num_format")
}

func getApplication(c *Client, applicationId string) *simplejson.Json {
	application := c.getData("applications/"+applicationId, url.Values{})
	return application
}

func getMyAppliction(c *Client) *simplejson.Json {
	application := c.getData("applications/me", url.Values{})
	return application
}

func createApplication(c *Client) string {
	res := c.postData("applications", url.Values{"name": {"李智维"}, "wechatAccount": {"lzwjava"},
		"socialAccount": {"GitHub@lzwjava, 微博@lzwjava"}, "introduction": {"21岁CEO，李智维，趣直播首席客服。知识直播平台，上线三个月用户1万。中学搞搞算法竞赛，大二休学到 LeanCloud 工作。大三联合创立了 Reviewcode.cn，现为趣直播运营狗、技术狗，搬砖中。"}})
	id := toStr(res.Get("applicationId").MustInt())
	return id
}

func TestApplications_succeed(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	applictionId := createApplication(c)
	c.admin = true
	res := c.postData("applications/"+applictionId+"/succeed", url.Values{})
	assert.NotNil(t, res.Interface())
}

func TestApplications_reject(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	applictionId := createApplication(c)
	c.admin = true
	res := c.postData("applications/"+applictionId+"/reject", url.Values{"reviewRemark": {"简介太短"}})
	assert.NotNil(t, res.Interface())
}

func TestApplications_rejectAndSubmit(t *testing.T) {
	c, _ := NewClientAndWeChatUser()
	applictionId := createApplication(c)
	c.admin = true
	res := c.postData("applications/"+applictionId+"/reject", url.Values{"reviewRemark": {"简介太短"}})
	assert.NotNil(t, res.Interface())

	updateRes := c.postData("applications/"+applictionId, url.Values{"introduction": {"我的简介怎么短了"}})
	assert.NotNil(t, updateRes.Interface())

	newApplication := getMyAppliction(c)
	assert.Equal(t, newApplication.Get("status").MustInt(), 1)
}
