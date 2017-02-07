package liveserver

import (
	"net/url"
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestAccounts_me(t *testing.T) {
	c, _ := NewClientAndUser()
	res := c.getData("accounts/me", url.Values{})
	assert.NotNil(t, res.Interface())
}

// func TestAccounts_initIncome(t *testing.T) {
// 	c := NewClient()
// 	c.admin = true
// 	res := c.getData("accounts/initIncome", url.Values{})
// 	assert.NotNil(t, res.Interface())
// }
//
