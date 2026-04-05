<?php

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

    function create_post()
    {
        if ($this->checkIfNotAdmin()) {
            return;
        }
        if ($this->checkIfParamsNotExist($this->post(), array(
            KEY_GROUP_USER_NAME,
            KEY_QRCODE_KEY,
            KEY_TOPIC_ID))
        ) {
            return;
        }
        $groupUserName = $this->post(KEY_GROUP_USER_NAME);
        $qrcodeKey = $this->post(KEY_QRCODE_KEY);
        $topicId = intval($this->post(KEY_TOPIC_ID));
        $id = $this->wechatGroupDao->createGroup($groupUserName, $qrcodeKey, $topicId);
        $this->succeed(array(KEY_GROUP_ID => $id));
    }

    function one_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_GROUP_USER_NAME))) {
            return;
        }
        $groupUserName = $this->get(KEY_GROUP_USER_NAME);
        $wechatGroup = $this->wechatGroupDao->queryGroup($groupUserName);
        $this->succeed($wechatGroup);
    }

    function current_get()
    {
        if ($this->checkIfParamsNotExist($this->get(), array(KEY_TOPIC_ID))) {
            return;
        }
        $topicId = $this->get(KEY_TOPIC_ID);
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

    function list_get()
    {
        $groups = $this->wechatGroupDao->allGroups();
        $this->succeed($groups);
    }

    function update_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_GROUP_USER_NAME,
            KEY_MEMBER_COUNT))
        ) {
            return;
        }
        $groupUserName = $this->post(KEY_GROUP_USER_NAME);
        $memberCount = intval($this->post(KEY_MEMBER_COUNT));
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