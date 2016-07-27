package codereview

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestLives_create(t *testing.T) {
	c := NewClient()
	res := c.post("lives", url.Values{"subject": {"直播啦"}})
	assert.NotNil(t, res)
}

func TestLives_livings(t *testing.T) {
	c := NewClient()
	res := c.get("lives/on", url.Values{})
	assert.NotNil(t, res)
}
