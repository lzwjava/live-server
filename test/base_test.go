package liveserver

import (
	"crypto/md5"
	"database/sql"
	"fmt"
	"math/rand"
	"net/url"
	"os"
	_ "reflect"
	"strconv"
	"testing"

	"time"

	"github.com/bitly/go-simplejson"
	_ "github.com/go-sql-driver/mysql"
)

func TestMain(m *testing.M) {
	rand.Seed(time.Now().Unix())
	os.Exit(m.Run())
}

func setUp() {
	cleanTables()
}

func cleanTables() {
	tables := []string{}
	deleteTable("comments", true)
	for _, table := range tables {
		deleteTable(table, false)
	}
	fmt.Println()
}

func checkErr(err error) {
	if err != nil {
		panic(err)
	}
}

func deleteTable(table string, noCheck bool) {
	deleteRecord(table, "1", "1", noCheck)
}

func runSqlNoCheck(sentence string) {
	runSql(sentence, false)
}

func runSql(sentence string, noCheck bool) {
	db, err := sql.Open("mysql", "lzw:@/qulive")
	checkErr(err)

	err = db.Ping()
	checkErr(err)

	var stmt *sql.Stmt
	var res sql.Result

	if noCheck {
		stmt, err = db.Prepare("SET FOREIGN_KEY_CHECKS=0")
		checkErr(err)

		res, err = stmt.Exec()
		checkErr(err)
	}

	stmt, err = db.Prepare(sentence)
	checkErr(err)

	res, err = stmt.Exec()
	checkErr(err)

	affect, err := res.RowsAffected()
	checkErr(err)

	fmt.Println(sentence, "affected", affect)

	if noCheck {
		stmt, err = db.Prepare("SET FOREIGN_KEY_CHECKS=1")
		checkErr(err)

		res, err = stmt.Exec()
		checkErr(err)
	}

	db.Close()
}

func queryDb(sentence string) *sql.Rows {
	db, err := sql.Open("mysql", "lzw:@/qulive")
	checkErr(err)
	defer db.Close()

	var stmt *sql.Stmt
	var res *sql.Rows

	stmt, err = db.Prepare(sentence)
	checkErr(err)

	res, err = stmt.Query()
	checkErr(err)

	return res
}

func deleteRecord(table string, column string, id string, noCheck bool) {
	sqlStr := fmt.Sprintf("delete from %s where %s=%s", table, column, id)
	runSql(sqlStr, noCheck)
}

func toInt(obj interface{}) int {
	if _, isFloat := obj.(float64); isFloat {
		return int(obj.(float64))
	} else {
		return obj.(int)
	}
}

func floatToStr(flt interface{}) string {
	return strconv.Itoa(toInt(flt))
}

func toStr(flt interface{}) string {
	return strconv.Itoa(toInt(flt))
}

// user

func randomMobile() string {
	var mobile = "13"
	for i := 0; i < 9; i++ {
		mobile += strconv.Itoa(rand.Intn(10))
	}
	return mobile
}

func randomString() string {
	return strconv.Itoa(rand.Intn(1000000000))
}

var letters = []rune("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789")

func RandAlnum(n int) string {
	rnd := rand.New(rand.NewSource(time.Now().UTC().UnixNano()))
	b := make([]rune, n)
	for i := range b {
		b[i] = letters[rnd.Intn(len(letters))]
	}
	return string(b)
}

func md5password(password string) string {
	data := []byte(password)
	return fmt.Sprintf("%x", md5.Sum(data))
}

func login(c *Client, mobilePhoneNumber string) *simplejson.Json {
	return c.postData("login", url.Values{"mobilePhoneNumber": {mobilePhoneNumber},
		"smsCode": {"5555"}})
}

func registerUserWithPhone(c *Client, mobilePhoneNumber string, username string) *simplejson.Json {
	res := c.post("users", url.Values{"mobilePhoneNumber": {mobilePhoneNumber},
		"username": {username}, "smsCode": {"5555"}, "avatarUrl": {"http://i.quzhiboapp.com/defaultAvatar1.png"}})
	if res.MustString("status") == "success" {
		registerRes := res.Get("result")
		c.SessionToken = registerRes.Get("sessionToken").MustString()
		return registerRes
	} else {
		loginRes := login(c, mobilePhoneNumber)
		return loginRes
	}
}

func registerUser(c *Client) *simplejson.Json {
	return registerUserWithPhone(c, "13261630925", "lzwjavaTest")
}

func registerUser2(c *Client) *simplejson.Json {
	return registerUserWithPhone(c, "18813106251", "满天星")
}

func registerNewUser(c *Client) *simplejson.Json {
	return registerUserWithPhone(c, randomMobile(), randomString())
}

func NewClientAndUser() (*Client, string) {
	c := NewClient()
	user := registerNewUser(c)
	return c, toStr(user.Get("userId").MustInt())
}

func NewClientAndWeChatUser() (*Client, string) {
	deleteSnsUser()
	c := NewClient()
	user := registerNewUser(c)
	userId := toStr(user.Get("userId").MustInt())
	insertSnsUser(userId)
	return c, userId
}

func NewClientAndWeChatUserWithName() (*Client, string) {
	deleteSnsUser()
	c := NewClient()
	user := registerNewUser(c)
	userId := toStr(user.Get("userId").MustInt())
	insertSnsUserWithUsername(userId, user.Get("username").MustString())
	return c, userId
}

func NewClientAndWeChatUser2() (*Client, string) {
	deleteSnsUser2()
	c := NewClient()
	user := registerNewUser(c)
	userId := toStr(user.Get("userId").MustInt())
	insertSnsUser2(userId)
	return c, userId
}
