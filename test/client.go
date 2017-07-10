package liveserver

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"io/ioutil"
	"net/http"
	"net/http/cookiejar"
	"net/url"
	"os"

	"mime/multipart"
	"path/filepath"

	"strings"

	"github.com/bitly/go-simplejson"
)

type Client struct {
	HTTPClient   *http.Client
	cookieJar    *cookiejar.Jar
	SessionToken string
	admin        bool
}

func NewClient() *Client {
	cookieJar, _ := cookiejar.New(nil)
	return &Client{
		HTTPClient: &http.Client{Jar: cookieJar},
		cookieJar:  cookieJar,
	}
}

func (c *Client) post(path string, params url.Values) *simplejson.Json {
	return c.request("POST", path, params)
}

func (c *Client) get(path string, params url.Values) *simplejson.Json {
	return c.request("GET", path, params)
}

func (c *Client) delete(path string) *simplejson.Json {
	return c.request("DELETE", path, url.Values{})
}

func (c *Client) patch(path string, params url.Values) *simplejson.Json {
	return c.request("PATCH", path, params)
}

func (c *Client) patchData(path string, params url.Values) *simplejson.Json {
	res := c.patch(path, params)
	return c.resultFromRes(res)
}

func (c *Client) patchArrayData(path string, params url.Values) *simplejson.Json {
	res := c.patch(path, params)
	return c.resultFromRes(res)
}

func (c *Client) postData(path string, params url.Values) *simplejson.Json {
	res := c.post(path, params)
	return c.resultFromRes(res)
}

func (c *Client) postArrayData(path string, params url.Values) *simplejson.Json {
	res := c.post(path, params)
	return c.resultFromRes(res)
}

func (c *Client) deleteData(path string) *simplejson.Json {
	var res = c.delete(path)
	return c.resultFromRes(res)
}

func (c *Client) deleteArrayData(path string) *simplejson.Json {
	var res = c.delete(path)
	return c.resultFromRes(res)
}

func (c *Client) getData(path string, params url.Values) *simplejson.Json {
	var res = c.get(path, params)
	return c.resultFromRes(res)
}

func (c *Client) getArrayData(path string, params url.Values) *simplejson.Json {
	var res = c.get(path, params)
	return c.resultFromRes(res)
}

func baseUrl(path string) string {
	// http://cleanbugs.com/item/412584/php-wechat-scan-code-payment-development-testing-on-some-computers-the.html
	var urlStr string
	urlStr = "http://localhost:3005/" + path
	// urlStr = "http://127.0.0.1:3005/" + path
	// urlStr = "http://localhost:8888/live-server/index.php/" + path
	return urlStr
}

func (c *Client) request(method string, path string, params url.Values) *simplejson.Json {
	req := c.genRequest(method, path, params)
	return c.doRequest(req)
}

func (c *Client) genRequest(method string, path string, params url.Values) *http.Request {
	urlStr := baseUrl(path)
	paramStr := bytes.NewBufferString(params.Encode())

	var req *http.Request
	var err error
	if method == "GET" {
		req, err = http.NewRequest(method, fmt.Sprintf("%s?%s", urlStr, paramStr), nil)
	} else if method == "POST" || method == "PATCH" {
		req, err = http.NewRequest(method, urlStr, paramStr)
		req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	} else {
		req, err = http.NewRequest(method, urlStr, paramStr)
	}
	checkErr(err)
	if c.admin {
		req.SetBasicAuth("admin", "Pwx9uVJM")
	}
	fmt.Println("curl -X", method, urlStr, params)
	return req
}

func (c *Client) postWithParams(path string, params url.Values) string {
	req := c.genRequest("POST", path, params)
	resp, err := c.HTTPClient.Do(req)
	checkErr(err)
	byteArr, err := ioutil.ReadAll(resp.Body)
	checkErr(err)
	bodyStr := string(byteArr)
	fmt.Println("response:", bodyStr)
	return bodyStr
}

func (c *Client) postWithStr(path string, body string) string {
	urlStr := baseUrl(path)
	req, err := http.NewRequest("POST", urlStr, strings.NewReader(body))
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	checkErr(err)
	fmt.Println("curl -X", "POST", urlStr, body)
	resp, err := c.HTTPClient.Do(req)
	checkErr(err)
	bodyStr := writeStringAndGet(resp.Body)
	fmt.Println("response:", bodyStr)
	return bodyStr
}

func (c *Client) doRequest(req *http.Request) *simplejson.Json {

	resp, err := c.HTTPClient.Do(req)
	checkErr(err)

	bodyStr := writeStringAndGet(resp.Body)

	resp.Body.Close()

	fmt.Println("response:", bodyStr)
	fmt.Println()

	var dat *simplejson.Json

	jsonErr := json.Unmarshal([]byte(bodyStr), &dat)
	checkErr(jsonErr)

	return dat
}

func (c *Client) newFileRequest(urlPath string, params url.Values,
	paramName string, path string) *simplejson.Json {
	file, err := os.Open(path)
	checkErr(err)
	defer file.Close()

	body := &bytes.Buffer{}
	writer := multipart.NewWriter(body)
	part, err := writer.CreateFormFile(paramName, filepath.Base(path))
	checkErr(err)
	_, err = io.Copy(part, file)
	for key, _ := range params {
		_ = writer.WriteField(key, params.Get(key))
	}
	err = writer.Close()
	checkErr(err)
	url := baseUrl(urlPath)
	req, err := http.NewRequest("POST", url, body)
	checkErr(err)
	req.Header.Add("Content-Type", writer.FormDataContentType())
	return c.doRequest(req)
}

func writeStringAndGet(body io.ReadCloser) string {
	buf := new(bytes.Buffer)
	buf.ReadFrom(body)
	bodyStr := buf.String()
	ioutil.WriteFile("error.html", []byte(bodyStr), 0644)
	return bodyStr
}

func (c *Client) resultFromRes(res *simplejson.Json) *simplejson.Json {
	if res.Get("status").MustString() != "success" {
		panic("status is not success")
	}
	return res.Get("result")
}

func readString(reader io.ReadCloser) string {
	buf := new(bytes.Buffer)
	buf.ReadFrom(reader)
	s := buf.String()
	return s
}
