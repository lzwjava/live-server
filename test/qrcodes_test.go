package liveserver

import (
	"net/url"
	"testing"

	"net/http"

	"io/ioutil"

	"fmt"

	"github.com/stretchr/testify/assert"
)

func code() string {
	return "quzhibo-" + RandAlnum(32)
}

func TestQrcodes_scan(t *testing.T) {
	c, _ := NewClientAndUser()

	code := code()

	res := c.getData("qrcodes/scanned", url.Values{"code": {code}})
	assert.False(t, res.Get("scanned").MustBool())

	res = c.postData("qrcodes", url.Values{"code": {code}})
	assert.NotNil(t, res)

	res = c.getData("qrcodes/scanned", url.Values{"code": {code}})
	assert.True(t, res.Get("scanned").MustBool())
}

func TestQrcodes_gen(t *testing.T) {
	c, _ := NewClientAndUser()
	code := code()
	fmt.Println(code)
	req, err := http.NewRequest("GET", baseUrl("qrcodes/gen?code="+code), nil)
	checkErr(err)
	resp, err := c.HTTPClient.Do(req)
	checkErr(err)
	byteArray, err := ioutil.ReadAll(resp.Body)
	checkErr(err)
	assert.NotNil(t, byteArray)
	assert.True(t, len(byteArray) > 700)
}
