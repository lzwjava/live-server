<?php

use AppModelsWechatGroupDao;
use AppModelsGroupDao;

namespace App\Controllers;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 4/7/17
 * Time: 3:42 AM
 */
class WechatGroups extends BaseController
{
    /**@var WechatGroupDao */
    public $wechatGroupDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(WechatGroupDao::class);
        $this->wechatGroupDao = new WechatGroupDao();
    }

    public function create()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(
            KEY_GROUP_USER_NAME,
            KEY_QRCODE_KEY,
            KEY_TOPIC_ID))
        ) {
            return;
        }
        $groupUserName = $this->request->getPost(KEY_GROUP_USER_NAME);
        $qrcodeKey = $this->request->getPost(KEY_QRCODE_KEY);
        $topicId = intval($this->request->getPost(KEY_TOPIC_ID));
        $id = $this->wechatGroupDao->createGroup($groupUserName, $qrcodeKey, $topicId);
        $this->succeed(array(KEY_GROUP_ID => $id));
    }

    public function one()
    {
        if ($this->checkIfParamsNotExist($this->request->getGet(), array(KEY_GROUP_USER_NAME))) {
            return;
        }
        $groupUserName = $this->request->getGet(KEY_GROUP_USER_NAME);
        $wechatGroup = $this->wechatGroupDao->queryGroup($groupUserName);
        $this->succeed($wechatGroup);
    }

    public function current()
    {
        if ($this->checkIfParamsNotExist($this->request->getGet(), array(KEY_TOPIC_ID))) {
            return;
        }
        $topicId = $this->request->getGet(KEY_TOPIC_ID);
        $group = $this->wechatGroupDao->currentGroup($topicId);
        if (!$group) {
            $this->failure(ERROR_NO_AVAILABLE_GROUP);
            return;
        }
        $this->succeed(array(
            KEY_GROUP_ID => $group->groupId,
            KEY_QRCODE_URL => $group->qrcodeUrl
        ));
    }

    public function list()
    {
        $groups = $this->wechatGroupDao->allGroups();
        $this->succeed($groups);
    }

    public function update()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_GROUP_USER_NAME,
            KEY_MEMBER_COUNT))
        ) {
            return;
        }
        $groupUserName = $this->request->getPost(KEY_GROUP_USER_NAME);
        $memberCount = intval($this->request->getPost(KEY_MEMBER_COUNT));
        $group = $this->wechatGroupDao->queryGroup($groupUserName);
        if (!$group) {
            $this->failure(ERROR_OBJECT_NOT_EXIST);
            return;
        }
        $this->wechatGroupDao->updateMemberCount($groupUserName, $memberCount);
        if ($memberCount > 95) {
            $this->wechatGroupDao->setUsed($groupUserName);
        }
        $this->succeed();
    }
}