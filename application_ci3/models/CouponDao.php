<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 11/7/16
 * Time: 9:58 PM
 */
class CouponDao extends BaseDao
{
    function addCoupon($phone, $liveId)
    {
        $data = array(
            KEY_LIVE_ID => $liveId,
            KEY_PHONE => $phone
        );
        $this->db->insert(TABLE_COUPONS, $data);
        return $this->db->insert_id();
    }

    function haveCoupon($phone, $liveId)
    {
        $sql = "SELECT count(*) AS cnt FROM coupons WHERE phone=? AND liveId=?";
        $binds = array(KEY_PHONE => sha1($phone), KEY_LIVE_ID => $liveId);
        $result = $this->db->query($sql, $binds)->row();
        return $result->cnt > 0;
    }

    function updateCouponUserId($phone, $liveId, $userId)
    {
        $sql = "UPDATE coupons SET userId =? WHERE phone=? AND liveId=?";
        $binds = array(
            KEY_USER_ID => $userId,
            KEY_PHONE => sha1($phone),
            KEY_LIVE_ID => $liveId
        );
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

}