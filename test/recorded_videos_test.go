package liveserver

// func TestRecordedVideos_convert(t *testing.T) {
// 	c, _ := NewClientAndUser()
// 	deleteTable("recorded_videos", false)
// 	liveId := createLive(c)
// 	genVideo(c, liveId, "GJDnouWr.1478744070441.flv")
// 	genVideo(c, liveId, "GJDnouWr.1478754070441.flv")
//
// 	res := c.getData("recordedVideos/convert", url.Values{"liveId": {liveId}})
// 	assert.NotNil(t, res.Interface())
// }

// func TestRecordedVideos_replay(t *testing.T) {
// 	c, _ := NewClientAndUser()
// 	deleteTable("recorded_videos", false)
// 	liveId := createLive(c)
// 	genVideo(c, liveId, "QFOkYbgj.1481841906000.flv")
//
// 	go func() {
// 		<-time.After(10 * time.Second)
// 		runSql("update lives set status=30 where liveId="+liveId, true)
// 	}()
//
// 	res := c.getData("recordedVideos/replay", url.Values{"liveId": {liveId}})
// 	assert.NotNil(t, res.Interface())
// }
