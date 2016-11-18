<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/12/3
 * Time: 下午8:32
 */
class Rewards extends BaseController
{
    public $alipay;
    public $payNotifyDao;
    public $liveDao;
    public $pay;
    public $snsUserDao;

    function __construct()
    {
        parent::__construct();
        $this->load->library('alipay/' . Alipay::class);
        $this->alipay = new Alipay();
        $this->load->model(PayNotifyDao::class);
        $this->payNotifyDao = new PayNotifyDao();
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
        $this->load->library(Pay::class);
        $this->pay = new Pay();
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
    }

    protected function checkIfRewardAmountWrong($amount)
    {
        if (is_int($amount) == false) {
            $this->failure(ERROR_AMOUNT_UNIT);
            return true;
        }
        if ($amount < LEAST_COMMON_REWARD) {
            $this->failure(ERROR_REWARD_TOO_LITTLE);
            return true;
        }
        if ($amount > MAX_COMMON_REWARD) {
            $this->failure(ERROR_REWARD_TOO_MUCH);
            return true;
        }
        return false;
    }

    public function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_LIVE_ID, KEY_AMOUNT, KEY_CHANNEL))) {
            return;
        }
        $liveId = $this->post(KEY_LIVE_ID);
        $amount = $this->post(KEY_AMOUNT);
        $channel = $this->post(KEY_CHANNEL);
        if ($this->checkIfRewardAmountWrong($amount)) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $live = $this->liveDao->getLive($liveId, $user);
        if ($this->checkIfObjectNotExists($live)) {
            return;
        }
        if ($this->checkIfNotInArray($channel, array(CHANNEL_ALIPAY_APP,
            CHANNEL_WECHAT_QRCODE, CHANNEL_WECHAT_H5))
        ) {
            return;
        }
        $metaData = array(KEY_TYPE => CHARGE_TYPE_REWARD,
            KEY_LIVE_ID => $liveId, KEY_USER_ID => $user->userId);
        $subject = '打赏主播';
        $body = '打赏主播';

        $openId = null;

        if ($channel == CHANNEL_WECHAT_H5) {
            $snsUser = $this->snsUserDao->getSnsUserByUser($user);
            if (!$snsUser) {
                $this->failure(ERROR_MUST_BIND_WECHAT);
                return;
            }
            $openId = $snsUser->openId;
        }

        $ch = $this->pay->createChargeAndInsert($amount, $channel, $subject, $body, $metaData, $user, $openId);
        if (!$ch) {
            $this->failure(ERROR_CHARGE_CREATE);
            return;
        }
        $this->succeed($ch);
    }

    public function notify_post()
    {
        $content = file_get_contents("php://input");
        logInfo("notify $content");
        if ($this->alipay->isSignVerify($_POST, $_POST['sign'])) {
            $outTradeNo = $_POST['out_trade_no'];
            $trade_status = $_POST['trade_status'];
            if ($trade_status == 'TRADE_SUCCESS') {
                $error = $this->payNotifyDao->handleChargeSucceed($outTradeNo);
                if ($error) {
                    logInfo("error: " . $error);
                    $this->failure($error);
                    return;
                }
            }
            echo 'success';
        } else {
            logInfo("sign failed");
            $this->failure(ERROR_SIGN_FAILED);
        }
    }

    function success_get()
    {
        $params = $this->get();
        $paramsStr = json_encode($params);
        logInfo("reward success $paramsStr");
    }

}
