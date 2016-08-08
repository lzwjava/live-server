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

func runSql(sentence string, noCheck bool) {
	db, err := sql.Open("mysql", "lzw:@/weimg")
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
	var mobile = "132"
	for i := 0; i < 8; i++ {
		mobile += strconv.Itoa(rand.Intn(10))
	}
	return mobile
}

func randomString() string {
	return strconv.Itoa(rand.Intn(100000))
}

func md5password(password string) string {
	data := []byte(password)
	return fmt.Sprintf("%x", md5.Sum(data))
}

func login(c *Client, mobilePhoneNumber string, password string) *simplejson.Json {
	return c.postData("login", url.Values{"mobilePhoneNumber": {mobilePhoneNumber},
		"password": {md5password(password)}})
}

func registerUserWithPhone(c *Client, mobilePhoneNumber string, username string) *simplejson.Json {
	res := c.post("users", url.Values{"mobilePhoneNumber": {mobilePhoneNumber},
		"username": {username}, "smsCode": {"5555"}, "password": {md5password("123456")}})
	if res.MustString("status") == "success" {
		registerRes := res.Get("result")
		c.SessionToken = registerRes.Get("sessionToken").MustString()
		return registerRes
	} else {
		loginRes := login(c, mobilePhoneNumber, "123456")
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
