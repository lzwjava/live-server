<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/21
 * Time: 下午4:38
 */
class ChargeDao extends BaseDao
{
    public $userDao;
    public $liveDao;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(UserDao::class);
        $this->userDao = new UserDao();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
    }


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
        $this->db->insert(TABLE_CHARGES, $data);
        $insertId = $this->db->insert_id();
        return $insertId;
    }

    function updateChargeToPaid($orderNo)
    {
        $this->db->where(KEY_ORDER_NO, $orderNo);
        $this->db->update(TABLE_CHARGES, array(KEY_PAID => 1));
        return $this->db->affected_rows() > 0;
    }

    function getOneByOrderNo($orderNo)
    {
        return $this->getOneFromTable(TABLE_CHARGES, KEY_ORDER_NO, $orderNo);
    }

    function updateRemark($orderNo, $remark)
    {
        $this->db->where(KEY_ORDER_NO, $orderNo);
        $this->db->update(TABLE_CHARGES, array(KEY_REMARK => $remark));
        return $this->db->affected_rows() > 0;
    }

    function countAdminList($orderNo, $creator)
    {
        if ($orderNo) {
            $this->db->where(KEY_ORDER_NO, $orderNo);
        }
        if ($creator) {
            $this->db->where(KEY_CREATOR, $creator);
        }
        return $this->db->count_all(TABLE_CHARGES);
    }

    function queryAdminList($skip = 0, $limit = 100, $orderNo, $creator)
    {
        if ($orderNo) {
            $this->db->where(KEY_ORDER_NO, $orderNo);
        }
        if ($creator) {
            $this->db->where(KEY_CREATOR, $creator);
        }
        $this->db->order_by(KEY_CHARGE_ID, 'desc');
        $this->db->limit($limit);
        $this->db->offset($skip);
        $list = $this->db->get(TABLE_CHARGES)->result();

        $total = $this->countAdminList($orderNo, $creator);

        foreach ($list as $item) {
            $metaData = json_decode($item->metaData);
            if ($metaData->type == 1) {
                $item->creatorUser = $this->userDao->findPublicUser('userId', $item->creator);
                $item->live = $this->liveDao->getLiveWithoutDetail($metaData->liveId);
            }
        }
        return array($list, $total);
    }

}
