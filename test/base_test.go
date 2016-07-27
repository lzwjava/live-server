package codereview

import (
	"database/sql"
	"fmt"
	"os"
	_ "reflect"
	"strconv"
	"testing"

	_ "github.com/go-sql-driver/mysql"
)

func TestMain(m *testing.M) {
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
