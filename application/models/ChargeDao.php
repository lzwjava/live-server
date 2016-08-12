<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/21
 * Time: 下午4:38
 */
class ChargeDao extends BaseDao
{
    public function add($orderNo, $amount, $creator, $creatorIP)
    {
        $data = array(
            KEY_ORDER_NO => $orderNo,
            KEY_AMOUNT => $amount,
            KEY_CREATOR => $creator,
            KEY_CREATOR_IP => $creatorIP
        );
        $this->db->insert(TABLE_CHARGES, $data);
        $insertId = $this->db->insert_id();
        return $insertId;
    }

    function updateChargeToPaid($orderNo)
    {
        $this->db->where(KEY_ORDER_NO, $orderNo);
        return $this->db->update(TABLE_CHARGES, array(KEY_PAID => 1));
    }

    function getOneByOrderNo($orderNo)
    {
        return $this->getOneFromTable(TABLE_CHARGES, KEY_ORDER_NO, $orderNo);
    }
}