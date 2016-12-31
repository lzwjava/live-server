<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/31/16
 * Time: 11:30 PM
 */
class PacketDao extends BaseDao
{
    function addPacket($userId, $totalAmount, $totalCount, $wishing)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_TOTAL_AMOUNT => $totalAmount,
            KEY_PACKET_ID => getToken(12),
            KEY_TOTAL_COUNT => $totalCount,
            KEY_WISHING => $wishing,
            KEY_REMAIN_COUNT => $totalCount,
            KEY_BALANCE => $totalAmount
        );
        $this->db->insert(TABLE_PACKETS, $data);
        return $this->db->insert_id();
    }

    function getPacket($packetId)
    {
        return $this->getOneFromTable(TABLE_PACKETS, KEY_PACKET_ID, $packetId);
    }

    function updatePacket($packetId, $balance, $originRemain)
    {
        $sql = "UPDATE packets SET balance = ?, remainCount = ? WHERE remainCount = ? AND packetId=?";
        $binds = array($balance, $originRemain - 1, $originRemain, $packetId);
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

    function getMyPacket($userId)
    {
        $sql = "SELECT * FROM packets WHERE userId=? ORDER BY created DESC LIMIT 1";
        $binds = array($userId);
        return $this->db->query($sql, $binds)->row();
    }
}