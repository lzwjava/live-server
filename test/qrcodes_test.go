package liveserver

import (
	"testing"

	"net/http"

	"io/ioutil"

	"fmt"

	"github.com/stretchr/testify/assert"
)

func TestQrcodes_one(t *testing.T) {
	c, _ := NewClientAndUser()
	code := "http://m.quzhiboapp.com/?liveId=1"
	fmt.Println(code)
	req, err := http.NewRequest("GET", baseUrl("qrcodes/one?text="+code), nil)
	checkErr(err)
	resp, err := c.HTTPClient.Do(req)
	checkErr(err)
	byteArray, err := ioutil.ReadAll(resp.Body)
	checkErr(err)
	assert.NotNil(t, byteArray)
	assert.True(t, len(byteArray) > 300)
}
