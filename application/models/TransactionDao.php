<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 8/16/16
 * Time: 1:06 PM
 */
class TransactionDao extends BaseDao
{
    public $accountDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(AccountDao::class);
        $this->accountDao = new AccountDao();
    }

    private function addTransaction($userId, $orderNo, $amount, $oldBalance, $type, $relatedId, $remark)
    {
        $data = array(
            KEY_USER_ID => $userId,
            KEY_ORDER_NO => $orderNo,
            KEY_AMOUNT => $amount,
            KEY_OLD_BALANCE => $oldBalance,
            KEY_TYPE => $type,
            KEY_RELATED_ID => $relatedId,
            KEY_REMARK => $remark
        );
        $this->db->insert(TABLE_TRANSACTIONS, $data);
        return $this->db->insert_id();
    }

    // must in transaction
    private function newTransaction($userId, $orderNo, $amount,
                                    $type, $relatedId, $remark, $isIncome = false)
    {
        $account = $this->accountDao->getOrCreateAccount($userId);
        $balance = $account->balance;
        $newBalance = $balance + $amount;
        if ($newBalance < 0) {
            return ERROR_BALANCE_INSUFFICIENT;
        }

        $transactionId = $this->addTransaction($userId, $orderNo, $amount, $balance, $type, $relatedId, $remark);

        if (!$transactionId) {
            return ERROR_TRANS_FAILED;
        }

        $updated = $this->accountDao->updateBalance($userId, $newBalance, $balance);
        if (!$updated) {
            return ERROR_TRANS_FAILED;
        }
        if ($isIncome) {
            $income = $account->income;
            $newIncome = $income + $amount;
            $incomeUpdated = $this->accountDao->updateIncome($userId, $newIncome, $income);
            if (!$incomeUpdated) {
                return ERROR_TRANS_FAILED;
            }
        }
        return null;
    }

    function newCharge($userId, $orderNo, $amount, $chargeId, $remark)
    {
        return $this->newTransaction($userId, $orderNo, $amount,
            TRANS_TYPE_RECHARGE, 'chargeId:' . $chargeId, $remark);
    }

    function newIncome($userId, $orderNo, $amount, $type, $liveId, $remark)
    {
        return $this->newTransaction($userId, $orderNo, $amount, $type,
            'liveId:' . $liveId, $remark, true);
    }

    function newPay($userId, $orderNo, $amount, $liveId, $remark)
    {
        return $this->newTransaction($userId, $orderNo, $amount, TRANS_TYPE_PAY,
            'liveId:' . $liveId, $remark);
    }

    function newPayPacket($userId, $orderNo, $amount, $packetId, $remark)
    {
        return $this->newTransaction($userId, $orderNo, $amount, TRANS_TYPE_PAY,
            'packetId:' . $packetId, $remark);
    }

    function newWithdraw($userId, $amount, $withdrawId)
    {
        $orderNo = genOrderNo();
        return $this->newTransaction($userId, $orderNo, -$amount, TRANS_TYPE_WITHDRAW,
            'withdrawId:' . $withdrawId, REMARK_WITHDRAW);
    }

}
