package liveserver

import (
	"fmt"
	"net/url"
	"testing"
	"time"

	"github.com/stretchr/testify/assert"
)

func TestJobs_alive(t *testing.T) {
	c := NewClient()
	res := c.getData("jobs/alive", url.Values{})
	assert.NotNil(t, res.Interface())
}

func runJob(c *Client) {
	c.getData("jobs/alive", url.Values{})
}

func queryLastJobId() int {
	rows := queryDb("select jobId from jobs order by created desc limit 1")
	defer rows.Close()
	rows.Next()
	var jobId int
	rows.Scan(&jobId)
	err := rows.Err()
	checkErr(err)
	return jobId
}

func setLastJobTriggerNow() {
	jobId := queryLastJobId()
	unix := time.Now().Unix()
	fmt.Printf("triggerTs = %d", unix)
	sql := "update jobs set triggerTs=%d where jobId=%d"
	runSql := fmt.Sprintf(sql, unix, jobId)
	runSqlNoCheck(runSql)
}

func TestJobs_notifyJob(t *testing.T) {

	c, _ := NewClientAndUser()
	liveId := createLive(c)

	setLastJobTriggerNow()

	c2, userId := NewClientAndWeChatUser()
	createWechatAttendance(c2, userId, liveId)

	runJob(c)
}
