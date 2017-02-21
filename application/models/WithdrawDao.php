<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 2/5/17
 * Time: 10:59 AM
 */
class WithdrawDao extends BaseDao
{
    function createWithdraw($userId, $amount)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_AMOUNT => $amount,
            KEY_STATUS => WITHDRAW_STATUS_WAIT
        );
        $this->db->insert(TABLE_WITHDRAWS, $data);
        return $this->db->insert_id();
    }

    function finishWithdraw($withdrawId)
    {
        return $this->updateStatus($withdrawId, WITHDRAW_STATUS_FINISH);
    }

    private function updateStatus($withdrawId, $status)
    {
        $data = array(
            KEY_STATUS => $status
        );
        $this->db->where(KEY_WITHDRAW_ID, $withdrawId);
        $this->db->update(TABLE_WITHDRAWS, $data);
        return $this->db->affected_rows() > 0;
    }

    function rejectWithdraw($withdrawId)
    {
        return $this->updateStatus($withdrawId, WITHDRAW_STATUS_REJECT);
    }

    function haveWaitWithdraw($userId)
    {
        $sql = "SELECT count(*) AS cnt FROM withdraws WHERE userId=? AND status=?";
        $binds = array(
            KEY_USER_ID => $userId,
            KEY_STATUS => WITHDRAW_STATUS_WAIT
        );
        $row = $this->db->query($sql, $binds)->row();
        return $row->cnt > 0;
    }

    function queryWaitWithdraw($userId)
    {
        $sql = "SELECT * FROM withdraws WHERE userId=? AND status=?";
        $binds = array(
            KEY_USER_ID => $userId,
            KEY_STATUS => WITHDRAW_STATUS_WAIT
        );
        $row = $this->db->query($sql, $binds)->row();
        return $row;
    }

    private function withdrawFields()
    {
        return array(KEY_WITHDRAW_ID, KEY_USER_ID, KEY_AMOUNT, KEY_STATUS, KEY_CREATED, KEY_UPDATED);
    }

    protected function withdrawPublicFields($prefix = TABLE_WITHDRAWS, $alias = false)
    {
        return $this->mergeFields($this->withdrawFields(), $prefix, $alias);
    }

    function queryWithdraws()
    {
        $withdrawFields = $this->withdrawPublicFields('w');
        $userFields = $this->userPublicFields('u', true);
        $sql = "SELECT $withdrawFields,$userFields FROM withdraws AS w LEFT JOIN users AS u ON u.userId=w.userId";
        $withdraws = $this->db->query($sql)->result();
        $this->assembleWithdraws($withdraws);
        return $withdraws;
    }

    private function assembleWithdraws($withdraws)
    {
        foreach ($withdraws as $withdraw) {
            $us = $this->prefixFields($this->userPublicRawFields(), 'u');
            $withdraw->user = extractFields($withdraw, $us, 'u');
        }
    }

    function queryWithdraw($withdrawId)
    {
        return $this->getOneFromTable(TABLE_WITHDRAWS, KEY_WITHDRAW_ID, $withdrawId);
    }

}
