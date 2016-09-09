<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 9/10/16
 * Time: 4:10 AM
 */
class StateDao extends BaseDao
{
    function addState($hash, $liveId)
    {
        $data = array(
            KEY_HASH => $hash,
            KEY_LIVE_ID => $liveId
        );
        $this->db->insert(TABLE_STATES, $data);
        return $this->db->insert_id();
    }

    function getState($hash)
    {
        return $this->getOneFromTable(TABLE_STATES, KEY_HASH, $hash);
    }

}
