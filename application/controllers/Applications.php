<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/31/16
 * Time: 3:19 PM
 */
class Applications extends BaseController
{
    public $applicationDao;
    public $wechatPlatform;

    function __construct()
    {
        parent::__construct();
        $this->load->model(ApplicationDao::class);
        $this->applicationDao = new ApplicationDao();
        $this->load->library(WeChatPlatform::class);
        $this->wechatPlatform = new WeChatPlatform();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_NAME, KEY_WECHAT_ACCOUNT,
            KEY_SOCIAL_ACCOUNT, KEY_INTRODUCTION))
        ) {
            return;
        }
        $name = $this->post(KEY_NAME);
        $wechatAccount = $this->post(KEY_WECHAT_ACCOUNT);
        $socialAccount = $this->post(KEY_SOCIAL_ACCOUNT);
        $introduction = $this->post(KEY_INTRODUCTION);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if (!$user->mobilePhoneNumber) {
            $this->failure(ERROR_ALREADY_BIND_PHONE);
            return;
        }
        $application = $this->applicationDao->getApplicationByUserId($user->userId);
        if ($application) {
            $this->failure(ERROR_ALREADY_APPLY);
            return;
        }
        if (mb_strlen($socialAccount) > MAX_SOCIAL_ACCOUNT_LEN) {
            $this->failure(ERROR_SOCIAL_ACCOUNT_ELN);
            return;
        }
        if (mb_strlen($introduction) > MAX_INTRODUCTION_LEN) {
            $this->failure(ERROR_INTRODUCTION_LEN);
            return;
        }
        $id = $this->applicationDao->create($user->userId, $name, $wechatAccount,
            $socialAccount, $introduction);
        if (!$id) {
            $this->failure(ERROR_SQL_WRONG);
        }
        $this->succeed(array(KEY_APPLICATION_ID => $id));
    }

    function update_post($applicationId)
    {
        $keys = array(KEY_NAME, KEY_WECHAT_ACCOUNT,
            KEY_SOCIAL_ACCOUNT, KEY_INTRODUCTION);
        if ($this->checkIfNotAtLeastOneParam($this->post(), $keys)
        ) {
            return;
        }
        $data = $this->postParams($keys);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $application = $this->applicationDao->getApplication($applicationId);
        if (!$application) {
            $this->failure(ERROR_OBJECT_NOT_EXIST);
            return;
        }
        $socialAccount = $this->post(KEY_SOCIAL_ACCOUNT);
        if ($socialAccount) {
            if (mb_strlen($socialAccount) > MAX_SOCIAL_ACCOUNT_LEN) {
                $this->failure(ERROR_SOCIAL_ACCOUNT_ELN);
                return;
            }
        }
        $introduction = $this->post(KEY_INTRODUCTION);
        if ($introduction) {
            if (mb_strlen($introduction) > MAX_INTRODUCTION_LEN) {
                $this->failure(ERROR_INTRODUCTION_LEN);
                return;
            }
        }
        $ok = $this->applicationDao->updateData($application->applicationId, $data);
        if ($ok) {
            $this->applicationDao->setReviewing($application->applicationId);
        }
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

    function reviewSucceed_post($applicationId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $ok = $this->applicationDao->setReviewSucceed($applicationId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $application = $this->applicationDao->getApplication($applicationId);
        $notifyOk = $this->wechatPlatform->notifyReviewByWeChat($application);
        if ($notifyOk) {
            $this->applicationDao->setReviewNotified($application->applicationId);
        }
        $this->succeed();
    }

    function reviewReject_post($applicationId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_REVIEW_REMARK))) {
            return;
        }
        $remark = $this->post(KEY_REVIEW_REMARK);
        if (mb_strlen($remark) > MAX_REVIEW_MARK_LEN) {
            $this->failure(ERROR_REVIEW_REMARK_LEN);
            return;
        }
        $ok = $this->applicationDao->setReviewReject($applicationId, $remark);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $application = $this->applicationDao->getApplication($applicationId);
        $notifyOk = $this->wechatPlatform->notifyReviewByWeChat($application);
        if ($notifyOk) {
            $this->applicationDao->setReviewNotified($application->applicationId);
        }
        $this->succeed();
    }

    function one_get($applicationId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $application = $this->applicationDao->getApplication($applicationId);
        $this->succeed($application);
    }

    function me_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $application = $this->applicationDao->getApplicationByUserId($user->userId);
        $this->succeed($application);
    }

}
