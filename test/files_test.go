package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestFiles_upToken(t *testing.T) {
	c := NewClient()
	res := c.getData("files/uptoken", url.Values{})
	assert.NotNil(t, res)
}
