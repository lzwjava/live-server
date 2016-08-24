package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestAlipay_sign(t *testing.T) {
	c := NewClient()
	res := c.postData("alipay/sign", url.Values{"partner": {"2088421737526755"}})
	assert.NotNil(t, res)
}
