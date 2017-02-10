<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 2/5/17
 * Time: 5:21 PM
 */
class Withdraws extends BaseController
{
    /** @var WithdrawDao */
    public $withdrawDao;

    /** @var SnsUserDao */
    public $snsUserDao;

    /** @var AccountDao */
    public $accountDao;

    /** @var LiveDao */
    public $liveDao;

    /** @var PayNotifyDao */
    public $payNotifyDao;

    /** @var WeChatPlatform */
    public $weChatPlatform;

    function __construct()
    {
        parent::__construct();
        $this->load->model(WithdrawDao::class);
        $this->withdrawDao = new WithdrawDao();
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $this->load->model(AccountDao::class);
        $this->accountDao = new AccountDao();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->model(PayNotifyDao::class);
        $this->payNotifyDao = new PayNotifyDao();
        $this->load->library(WeChatPlatform::class);
        $this->weChatPlatform = new WeChatPlatform();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_AMOUNT))) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $amount = $this->toNumber($this->post(KEY_AMOUNT));
        list($error, $data) = $this->createWithdraw($user, $amount);
        if ($error) {
            $this->failure($error);
            return;
        }
        $this->succeed($data);
    }

    private function createWithdraw($user, $amount)
    {
        if (!$user->wechatSubscribe) {
            return array(ERROR_MUST_SUBSCRIBE, null);
        }
        $snsUser = $this->snsUserDao->getSnsUserByUser($user);
        if (!$snsUser) {
            return array(ERROR_SNS_USER_NOT_EXISTS, null);
        }
        $account = $this->accountDao->getOrCreateAccount($user->userId);
        if ($amount > $account->balance) {
            return array(ERROR_EXCEED_BALANCE, null);
        }
        if ($amount < MIN_WITHDRAW_AMOUNT) {
            return array(ERROR_WITHDRAW_AMOUNT_TOO_LITTLE, null);
        }
        $haveWaitWithdraw = $this->withdrawDao->haveWaitWithdraw($user->userId);
        if ($haveWaitWithdraw) {
            return array(ERROR_HAVE_WAIT_WITHDRAW, null);
        }
        $haveWaitLive = $this->liveDao->haveWaitLive($user->userId);
        if ($haveWaitLive) {
            return array(ERROR_HAVE_WAIT_LIVE, null);
        }
        $withdrawId = $this->withdrawDao->createWithdraw($user->userId, $amount);
        if (!$withdrawId) {
            return array(ERROR_SQL_WRONG, null);
        }
        $withdraw = $this->withdrawDao->queryWithdraw($withdrawId);
        $this->weChatPlatform->notifyNewWithdraw($withdraw);
        $data = array(KEY_WITHDRAW_ID => $withdrawId);
        return array(null, $data);
    }

    function list_get()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $withdraws = $this->withdrawDao->queryWithdraws();
        $this->succeed($withdraws);
    }

    function agree_get($withdrawId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $error = $this->payNotifyDao->handleWithdraw($withdrawId, true);
        if ($error) {
            $this->failure($error);
            return;
        }
        $this->succeed();
    }

    function createByManual_post()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_AMOUNT,
            KEY_USER_ID, KEY_TRANSFER))
        ) {
            return;
        }
        $transfer = boolval($this->post(KEY_TRANSFER));
        $amount = intval($this->post(KEY_AMOUNT));
        $userId = $this->post(KEY_USER_ID);
        list($error, $data) = $this->manualWithdraw($userId, $amount, $transfer);
        if ($error) {
            $this->failure($error);
            return;
        }
        $this->succeed($data);
    }

    private function manualWithdraw($userId, $amount, $transfer)
    {
        $user = $this->userDao->findUserById($userId);
        if (!$user) {
            return array(ERROR_OBJECT_NOT_EXIST, null);
        }
        list($error, $data) = $this->createWithdraw($user, $amount);
        if ($error) {
            return array($error, null);
        }
        $withdrawId = $data[KEY_WITHDRAW_ID];
        $error = $this->payNotifyDao->handleWithdraw($withdrawId, $transfer);
        if ($error) {
            return array($error, null);
        }
        return array(null, $data);
    }

    function withdrawAll_get()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $accounts = $this->accountDao->queryAccountsHaveBalance();
        $succeedCount = 0;
        foreach ($accounts as $account) {
            $amount = $account->balance;
            if ($account->userId == ADMIN_OP_SYSTEM_ID) {
                continue;
            }
            $user = $this->userDao->findUserById($account->userId);
            if (!$user) {
                logInfo("user not exists");
                continue;
            }
            list($error, $data) = $this->createWithdraw($user, $amount);
            if ($error) {
                logInfo("create withdraw userId $account->userId  error:  $error");
            } else {
                $withdrawId = $data[KEY_WITHDRAW_ID];
                $error = $this->payNotifyDao->handleWithdraw($withdrawId, true);
                if ($error) {
                    logInfo("handle withdraw error userId $account->userId error:  $error");
                } else {
                    $succeedCount++;
                }
            }
        }
        logInfo("succeedCount: " . $succeedCount . ' total: ' . count($accounts));
        $this->succeed();
    }

}
