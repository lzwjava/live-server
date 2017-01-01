<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/31/16
 * Time: 11:30 PM
 */
class PacketDao extends BaseDao
{
    function addPacket($userId, $totalAmount, $totalCount, $wishing, $orderNo)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_TOTAL_AMOUNT => $totalAmount,
            KEY_PACKET_ID => getToken(12),
            KEY_ORDER_NO => $orderNo,
            KEY_TOTAL_COUNT => $totalCount,
            KEY_WISHING => $wishing,
            KEY_REMAIN_COUNT => $totalCount,
            KEY_BALANCE => $totalAmount
        );
        $this->db->insert(TABLE_PACKETS, $data);
        return $this->db->insert_id();
    }

    function getPacketById($packetId)
    {
        $packets = $this->getPacketsByIds(array($packetId));
        if (count($packets) > 0) {
            return $packets[0];
        } else {
            return null;
        }
    }

    function getPacketsByIds($packetIds)
    {
        if (count($packetIds) <= 0) {
            return null;
        }
        $userFields = $this->userPublicFields('u', true);
        $sql = "SELECT p.*, $userFields FROM packets AS p
                LEFT JOIN users AS u ON u.userId=p.userId
                WHERE p.packetId in ('" . implode("','", $packetIds) . "')
                order by p.created desc";
        $packets = $this->db->query($sql)->result();
        foreach ($packets as $packet) {
            $us = $this->prefixFields($this->userPublicRawFields(), 'u');
            $packet->user = extractFields($packet, $us, 'u');
        }
        return $packets;
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
        $sql = "SELECT packetId FROM packets WHERE userId=? ORDER BY created DESC LIMIT 1";
        $binds = array($userId);
        $packet = $this->db->query($sql, $binds)->row();
        if ($packet) {
            return $this->getPacketById($packet->packetId);
        } else {
            return null;
        }
    }

    private function extractPacketIds($packets)
    {
        $ids = array();
        foreach ($packets as $packet) {
            array_push($ids, $packet->packetId);
        }
        return $ids;
    }

    function getMyPackets($userId)
    {
        $sql = "SELECT packetId FROM packets WHERE userId=?";
        $binds = array($userId);
        $packets = $this->db->query($sql, $binds)->result();
        $packetIds = $this->extractPacketIds($packets);
        return $this->getPacketsByIds($packetIds);
    }

}
