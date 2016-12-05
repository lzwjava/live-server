<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/5/16
 * Time: 11:13 AM
 */
class LiveViewDao extends BaseDao
{

    function addLiveView($userId, $liveId, $platform, $liveStatus)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_LIVE_ID => $liveId,
            KEY_PLATFORM => $platform,
            KEY_LIVE_STATUS => $liveStatus
        );
        $this->db->insert(TABLE_LIVE_VIEWS, $data);
        return $this->db->insert_id();
    }

    function endLiveView($liveViewId)
    {
        $this->db->where(KEY_LIVE_VIEW_ID, $liveViewId);
        $data = array(
            KEY_ENDED => 1,
            KEY_END_TS => date('Y-m-d H:i:s')
        );
        $this->db->update(TABLE_LIVE_VIEWS, $data);
        return $this->db->affected_rows() > 0;
    }

}
