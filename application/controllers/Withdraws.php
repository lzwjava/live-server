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
        if (!$user->wechatSubscribe) {
            $this->failure(ERROR_MUST_SUBSCRIBE);
            return;
        }
        $amount = $this->toNumber($this->post(KEY_AMOUNT));
        $snsUser = $this->snsUserDao->getSnsUserByUser($user);
        if (!$snsUser) {
            $this->failure(ERROR_SNS_USER_NOT_EXISTS);
            return;
        }
        $account = $this->accountDao->getOrCreateAccount($user->userId);
        if ($amount > $account->balance) {
            $this->failure(ERROR_EXCEED_BALANCE);
            return;
        }
        if ($amount < MIN_WITHDRAW_AMOUNT) {
            $this->failure(ERROR_WITHDRAW_AMOUNT_TOO_LITTLE);
            return;
        }
        $haveWaitWithdraw = $this->withdrawDao->haveWaitWithdraw($user->userId);
        if ($haveWaitWithdraw) {
            $this->failure(ERROR_HAVE_WAIT_WITHDRAW);
            return;
        }
        $haveWaitLive = $this->liveDao->haveWaitLive($user->userId);
        if ($haveWaitLive) {
            $this->failure(ERROR_HAVE_WAIT_LIVE);
            return;
        }
        $withdrawId = $this->withdrawDao->createWithdraw($user->userId, $amount);
        $this->succeed(array(KEY_WITHDRAW_ID => $withdrawId));
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

        $error = $this->payNotifyDao->handleWithdraw($withdrawId);
        if ($error) {
            $this->failure($error);
            return;
        }
        $this->succeed();
    }

}
