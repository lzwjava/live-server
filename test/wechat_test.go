package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestWeChat_sign(t *testing.T) {
	c := NewClient()
	res := c.getData("wechat/sign", url.Values{})
	assert.NotNil(t, res)
	assert.NotNil(t, res.Get("appId").Interface())
	assert.NotNil(t, res.Get("nonceStr").Interface())
}

func TestWeChat_register(t *testing.T) {
	c := NewClient()
	res := c.post("wechat/register", url.Values{"code": {"001ONMjt1kPSE806Edjt18MSjt1ONMjS"}})
	assert.NotNil(t, res)
}
