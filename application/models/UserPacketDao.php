<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/31/16
 * Time: 11:48 PM
 */
class UserPacketDao extends BaseDao
{
    function addUserPacket($userId, $packetId, $amount)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_PACKET_ID => $packetId,
            KEY_AMOUNT => $amount
        );
        $this->db->insert(TABLE_USER_PACKETS, $data);
        return $this->db->insert_id();
    }

    function getUserPacket($userId, $packetId)
    {
        $sql = "SELECT * FROM user_packets WHERE userId=? AND packetId=?";
        $binds = array($userId, $packetId);
        return $this->db->query($sql, $binds)->row();
    }

    function sendSucceed($userPacketId)
    {
        $sql = "UPDATE user_packets SET sended = 1 WHERE userPacketId=?";
        $binds = array($userPacketId);
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

    function getUserPackets($packetId)
    {
        $userPublicFields = $this->userPublicFields('u', true);
        $sql = "SELECT up.*,$userPublicFields FROM user_packets AS up
                LEFT JOIN users AS u ON up.userId = u.userId
                WHERE packetId=? ORDER BY created DESC";
        $binds = array($packetId);
        $userPackets = $this->db->query($sql, $binds)->result();
        return $this->assembleUserPackets($userPackets);
    }

    function assembleUserPackets($userPackets)
    {
        $us = $this->prefixFields($this->userPublicRawFields(), 'u');
        foreach ($userPackets as $userPacket) {
            $userPacket->user = extractFields($userPacket, $us, 'u');
        }
        return $userPackets;
    }
}