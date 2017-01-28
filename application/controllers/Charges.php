<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/28/16
 * Time: 4:53 AM
 */
class Charges extends BaseController
{
    /** @var ChargeDao */
    public $chargeDao;

    /** @var Pay */
    public $pay;

    /** @var PayNotifyDao */
    public $payNotifyDao;

    /** @var SnsUserDao */
    public $snsUserDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(ChargeDao::class);
        $this->chargeDao = new ChargeDao();
        $this->load->library(Pay::class);
        $this->pay = new Pay();
        $this->load->model(SnsUserDao::class);
        $this->snsUserDao = new SnsUserDao();
        $this->load->model(PayNotifyDao::class);
        $this->payNotifyDao = new PayNotifyDao();
    }

    function one_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_ORDER_NO))) {
            return;
        }
        $orderNo = $this->get(KEY_ORDER_NO);
        $charge = $this->chargeDao->getOneByOrderNo($orderNo);
        $this->succeed($charge);
    }

    function remark_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_ORDER_NO, KEY_REMARK))) {
            return;
        }
        $orderNo = $this->post(KEY_ORDER_NO);
        $remark = $this->post(KEY_REMARK);

        $ok = $this->chargeDao->updateRemark($orderNo, $remark);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

    function create_post()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_CHANNEL, KEY_AMOUNT))) {
            return;
        }
        $channel = $this->post(KEY_CHANNEL);
        $amount = $this->toNumber($this->post(KEY_AMOUNT));
        if ($this->checkIfNotInArray($channel, channelSet())
        ) {
            return;
        }

        list($error, $openId) = $this->snsUserDao->getOpenIdByChannel($user, $channel);
        if ($error) {
            $this->failure($error);
            return;
        }

        $subject = '充值账户';
        $body = '充值账户';
        $metaData = array(KEY_TYPE => CHARGE_TYPE_BALANCE, KEY_USER_ID => $user->userId);

        list($error, $ch) = $this->pay->createChargeAndInsert($amount, $channel, $subject,
            $body, $metaData, $user, $openId);
        if ($error) {
            $this->failure(ERROR_CHARGE_CREATE, $error);
            return;
        }
        $this->succeed($ch);
    }

    function appleCallback_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_RECEIPT, KEY_ORDER_NO))) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $receipt = $this->post(KEY_RECEIPT);
        $orderNo = $this->post(KEY_ORDER_NO);
        $prodUrl = 'https://buy.itunes.apple.com/verifyReceipt';
        $testUrl = 'https://sandbox.itunes.apple.com/verifyReceipt';
        try {
            $respData = $this->getReceiptData($prodUrl, $receipt);
            if ($respData->status == 21007) {
                $respData = $this->getReceiptData($testUrl, $receipt);
            }
            if ($respData->status != 0) {
                $this->failureWithExtraMsg(ERROR_VERIFY_RECEIPT, $respData->status);
                return;
            }
            $error = $this->payNotifyDao->handleChargeSucceed($orderNo);
            if ($error) {
                $this->failure($error);
                return;
            }
            $this->chargeDao->updateRemark($orderNo, json_encode($respData->receipt));
            $this->succeed();
        } catch (Exception $e) {
            $this->failureWithExtraMsg(ERROR_VERIFY_RECEIPT, $e->getMessage());
        }
    }

    private function getReceiptData($url, $receipt)
    {
        $postData = json_encode(
            array('receipt-data' => $receipt)
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close($ch);
        if ($errno != 0) {
            throw new Exception($errmsg, $errno);
        }
        $data = json_decode($response);
        if (!is_object($data)) {
            throw new Exception('Invalid response data');
        }
        if (isDebug()) {
            logInfo("in debug, real data: " . json_encode($data));
            $debugData = new StdClass;
            $debugData->status = 0;
            $debugData->receipt = new StdClass;
            $debugData->receipt->quantity = 1;
            return $debugData;
        }
        return $data;
    }
}
