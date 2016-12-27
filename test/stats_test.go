package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestStats_all(t *testing.T) {
	c := NewClient()
	res := c.getData("stats/all", url.Values{})
	assert.NotNil(t, res)
	assert.True(t, res.Get("users").MustInt() > 0)
}
