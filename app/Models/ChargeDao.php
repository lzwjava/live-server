<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/21
 * Time: 下午4:38
 */
class ChargeDao extends BaseDao
{
    protected $table = 'charges';

    public function add($orderNo, $amount, $channel, $creator, $creatorIP, $metaData, $prepayId)
    {
        if (!$prepayId) {
            $prepayId = '';
        }
        $data = array(
            KEY_ORDER_NO => $orderNo,
            KEY_AMOUNT => $amount,
            KEY_CHANNEL => $channel,
            KEY_CREATOR => $creator,
            KEY_CREATOR_IP => $creatorIP,
            KEY_META_DATA => json_encode($metaData),
            KEY_PREPAY_ID => $prepayId
        );
        $this->db->table(TABLE_CHARGES)->insert($data);
        $insertId = $this->db->insertID();
        return $insertId;
    }

    function updateChargeToPaid($orderNo)
    {
        return $this->db->table(TABLE_CHARGES)->where(KEY_ORDER_NO, $orderNo)->update(array(KEY_PAID => 1)) !== false;
    }

    function getOneByOrderNo($orderNo)
    {
        return $this->getOneFromTable(TABLE_CHARGES, KEY_ORDER_NO, $orderNo);
    }

    function updateRemark($orderNo, $remark)
    {
        return $this->db->table(TABLE_CHARGES)->where(KEY_ORDER_NO, $orderNo)->update(array(KEY_REMARK => $remark)) !== false;
    }

}

// Namespace bridge: allow App\Libraries\ChargeDao → App\Models\ChargeDao
class_alias('App\Models\ChargeDao', 'App\Libraries\ChargeDao');
