<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/27/16
 * Time: 11:44 PM
 */
class LiveDao extends BaseDao
{

    private function genLiveKey()
    {
        return random_string('alnum', 8);
    }

    function createLive($subject)
    {
        $key = $this->genLiveKey();
        $data = array(
            KEY_SUBJECT => $subject,
            KEY_KEY => $key,
            KEY_STATUS => LIVE_STATUS_ON
        );
        $this->db->insert(TABLE_LIVE, $data);
        return $this->db->insert_id();
    }

    function getLive($id)
    {
        return $this->getOneFromTable(TABLE_LIVE, KEY_ID, $id);
    }
}
