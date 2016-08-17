package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestQrcodes_scan(t *testing.T) {
	c, _ := NewClientAndUser()

	code := "quzhibo-" + RandAlnum(32)

	res := c.getData("qrcodes/scanned", url.Values{"code": {code}})
	assert.False(t, res.Get("scanned").MustBool())

	res = c.postData("qrcodes", url.Values{"code": {code}})
	assert.NotNil(t, res)

	res = c.getData("qrcodes/scanned", url.Values{"code": {code}})
	assert.True(t, res.Get("scanned").MustBool())
}
