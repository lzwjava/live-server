<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/27/16
 * Time: 11:44 PM
 */
class LiveDao extends BaseDao
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('string');
    }

    private function genLiveKey()
    {
        return random_string('alnum', 8);
    }

    function createLive($subject, $coverUrl)
    {
        $key = $this->genLiveKey();
        $data = array(
            KEY_SUBJECT => $subject,
            KEY_KEY => $key,
            KEY_STATUS => LIVE_STATUS_ON,
            KEY_COVER_URL => $coverUrl
        );
        $this->db->insert(TABLE_LIVE, $data);
        return $this->db->insert_id();
    }

    function getLive($id)
    {
        $live = $this->getOneFromTable(TABLE_LIVE, KEY_ID, $id);
        if ($live != null) {
            $this->assembleLives(array($live));
        }
        return $live;
    }

    function getLivingLives()
    {
        $lives = $this->getListFromTable(TABLE_LIVE, KEY_STATUS, LIVE_STATUS_ON,
            '*', 'begin_ts desc');
        $this->assembleLives($lives);
        return $lives;
    }

    private function assembleLives($lives)
    {
        foreach ($lives as $live) {
            $live->rtmpUrl = "rtmp://hotimg.cn/live/" . $live->key;
        }
    }

    function update($id, $data)
    {
        $this->db->where(KEY_ID, $id);
        return $this->db->update(TABLE_LIVE, $data);
    }

    function endLive($id)
    {
        return $this->update($id, array(
            KEY_END_TS => date('Y-m-d H:i:s'),
            KEY_STATUS => LIVE_STATUS_OFF
        ));
    }


}
