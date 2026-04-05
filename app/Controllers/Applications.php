<?php

use AppModelsApplicationDao;

namespace App\Controllers;

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

    public function create()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_NAME, KEY_WECHAT_ACCOUNT,
            KEY_SOCIAL_ACCOUNT, KEY_INTRODUCTION))
        ) {
            return;
        }
        $name = $this->request->getPost(KEY_NAME);
        $wechatAccount = $this->request->getPost(KEY_WECHAT_ACCOUNT);
        $socialAccount = $this->request->getPost(KEY_SOCIAL_ACCOUNT);
        $introduction = $this->request->getPost(KEY_INTRODUCTION);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if (!$user->mobilePhoneNumber) {
            $this->failure(ERROR_MUST_BIND_PHONE);
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
        if (mb_strlen($introduction) < MIN_INTRODUCTION_LEN) {
            $this->failure(ERROR_MIN_INTRODUCTION_LEN);
            return;
        }
        if (!preg_match('/[a-zA-Z0-9]+/', $wechatAccount)) {
            $this->failure(ERROR_WECHAT_NUM_FORMAT);
            return;
        }
        $id = $this->applicationDao->create($user->userId, $name, $wechatAccount,
            $socialAccount, $introduction);
        if (!$id) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed(array(KEY_APPLICATION_ID => $id));
    }

    public function update($applicationId)
    {
        $keys = array(KEY_NAME, KEY_WECHAT_ACCOUNT,
            KEY_SOCIAL_ACCOUNT, KEY_INTRODUCTION);
        if ($this->checkIfNotAtLeastOneParam($this->request->getPost(), $keys)
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
        $socialAccount = $this->request->getPost(KEY_SOCIAL_ACCOUNT);
        if ($socialAccount) {
            if (mb_strlen($socialAccount) > MAX_SOCIAL_ACCOUNT_LEN) {
                $this->failure(ERROR_SOCIAL_ACCOUNT_ELN);
                return;
            }
        }
        $introduction = $this->request->getPost(KEY_INTRODUCTION);
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
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_REVIEW_REMARK))) {
            return;
        }
        $remark = $this->request->getPost(KEY_REVIEW_REMARK);
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

    public function one($applicationId)
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        $application = $this->applicationDao->getApplication($applicationId);
        $this->succeed($application);
    }

    public function me()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $application = $this->applicationDao->getApplicationByUserId($user->userId);
        $this->succeed($application);
    }

}
