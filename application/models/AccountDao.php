<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 8/16/16
 * Time: 1:07 PM
 */
class AccountDao extends BaseDao
{
    private function addAccount($userId)
    {
        $data = array(KEY_USER_ID => $userId);
        $this->db->insert(TABLE_ACCOUNTS, $data);
        return $this->db->insert_id();
    }

    private function fields()
    {
        return array(KEY_ACCOUNT_ID, KEY_USER_ID, KEY_BALANCE, KEY_INCOME, KEY_CREATED, KEY_UPDATED);
    }

    private function publicFields($prefix = TABLE_ACCOUNTS, $alias = false)
    {
        return $this->mergeFields($this->fields(), $prefix, $alias);
    }

    private function getAccount($userId)
    {
        return $this->getOneFromTable(TABLE_ACCOUNTS, KEY_USER_ID, $userId, $this->publicFields());
    }

    function getOrCreateAccount($userId)
    {
        $account = $this->getAccount($userId);
        if (!$account) {
            $this->addAccount($userId);
            return $this->getAccount($userId);
        } else {
            return $account;
        }
    }

    function updateBalance($userId, $balance, $oldBalance)
    {
        $sql = 'UPDATE accounts SET balance = ? WHERE userId=? AND balance=?';
        $binds = array($balance, $userId, $oldBalance);
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

    function updateIncome($userId, $income, $oldIncome)
    {
        $sql = 'UPDATE accounts SET income = ? WHERE userId=? AND income=?';
        $binds = array($income, $userId, $oldIncome);
        $this->db->query($sql, $binds);
        return $this->db->affected_rows() > 0;
    }

    private function queryAccountsHaveBalance()
    {
        $sql = "SELECT * FROM accounts WHERE balance > 0";
        $accounts = $this->db->query($sql)->result();
        return $accounts;
    }

    function initIncome()
    {
        $accounts = $this->queryAccountsHaveBalance();
        foreach ($accounts as $account) {
            $income = $account->balance;
            $oldIncome = $account->income;
            $updated = $this->updateIncome($account->userId, $income, $oldIncome);
            if (!$updated) {
                return false;
            }
        }
        return true;
    }

}
