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

    function createLive($ownerId, $subject)
    {
        $key = $this->genLiveKey();
        $data = array(
            KEY_OWNER_ID => $ownerId,
            KEY_SUBJECT => $subject,
            KEY_RTMP_KEY => $key,
            KEY_STATUS => LIVE_STATUS_PREPARE,
        );
        $this->db->insert(TABLE_LIVES, $data);
        return $this->db->insert_id();
    }

    function getLive($id)
    {
        $live = $this->getOneFromTable(TABLE_LIVES, KEY_LIVE_ID, $id);
        if ($live != null) {
            $this->assembleLives(array($live));
        }
        return $live;
    }

    function getLivingLives()
    {
        $lives = $this->getListFromTable(TABLE_LIVES, KEY_STATUS, LIVE_STATUS_ON,
            '*', 'begin_ts desc');
        $this->assembleLives($lives);
        return $lives;
    }

    private function assembleLives($lives)
    {
        foreach ($lives as $live) {
            $live->rtmpUrl = "rtmp://hotimg.cn/live/" . $live->rtmpKey;
        }
    }

    function update($id, $data)
    {
        $this->db->where(KEY_LIVE_ID, $id);
        return $this->db->update(TABLE_LIVES, $data);
    }

    function endLive($id)
    {
        return $this->update($id, array(
            KEY_END_TS => date('Y-m-d H:i:s'),
            KEY_STATUS => LIVE_STATUS_OFF
        ));
    }

    function beginLive($id)
    {
        return $this->update($id, array(
            KEY_BEGIN_TS => date('Y-m-d H:i:s'),
            KEY_STATUS => LIVE_STATUS_ON
        ));
    }

    function publishLive($id)
    {
        return $this->update($id, array(
            KEY_STATUS => LIVE_STATUS_WAIT
        ));
    }

}
