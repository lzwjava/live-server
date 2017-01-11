<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/9/17
 * Time: 6:08 PM
 */
class Apps extends BaseController
{
    public $appDao;
    public $appImgDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(AppDao::class);
        $this->appDao = new AppDao();
        $this->load->model(AppImgDao::class);
        $this->appImgDao = new AppImgDao();
    }

    function create_post()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $appId = $this->appDao->create($user->username . '的小程序', $user->userId);
        $this->succeed(array(KEY_APP_ID => $appId));
    }

    function update_post($appId)
    {
        $keys = array(KEY_NAME, KEY_QRCODE_KEY, KEY_APP_URL, KEY_SHORT_DESC, KEY_DESC, KEY_ICON_KEY);
        $data = $this->postParams($keys);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $app = $this->appDao->getApp($appId);
        if ($this->checkIfObjectNotExists($app)) {
            return;
        }
        if ($user->userId != $app->userId) {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        $this->appDao->updateApp($appId, $data);
        $this->succeed();
    }

    function updateImg_post($appId)
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_IMG_KEY, KEY_OP))) {
            return;
        }
        $op = $this->post(KEY_OP);
        $imgKey = $this->post(KEY_IMG_KEY);
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $app = $this->appDao->getApp($appId);
        if ($app->userId != $user->userId) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $ok = null;
        if ($op == 'add') {
            $ok = $this->appImgDao->addAppImg($appId, $imgKey);
        } else if ($op == 'remove') {
            $ok = $this->appImgDao->removeAppImg($appId, $imgKey);
        }
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

    function homeList_get()
    {
        $this->appDao->findApps();
    }

    function myList_get()
    {
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $apps = $this->appDao->findMyApps($user->userId);
        $this->succeed($apps);
    }

    function one_get($appId)
    {
        $app = $this->appDao->getApp($appId);
        if ($this->checkIfObjectNotExists($app)) {
            return;
        }
        $this->succeed($app);
    }

}
