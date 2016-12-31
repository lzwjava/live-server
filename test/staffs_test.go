package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestStaffs_create(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.postData("staffs", url.Values{"key": {"BornToBeProud"}})
	assert.NotNil(t, res.Interface())
}

func createStaff(c *Client) {
	c.postData("staffs", url.Values{"key": {"BornToBeProud"}})
}

func TestStaffs_list(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.getData("staffs", url.Values{})
	assert.NotNil(t, res.Interface())
}
