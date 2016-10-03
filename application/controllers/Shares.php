<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 10/4/16
 * Time: 4:50 AM
 */
class Shares extends BaseController
{

    /** @var ShareDao */
    public $shareDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(ShareDao::class);
        $this->shareDao = new ShareDao();
    }

    function create_post()
    {
        if ($this->checkIfParamsNotExist($this->post(), array(KEY_LIVE_ID,
            KEY_SHARE_TS, KEY_CHANNEL))
        ) {
            return;
        }
        $liveId = $this->post(KEY_LIVE_ID);
        $shareTs = $this->post(KEY_SHARE_TS);
        $channel = $this->post(KEY_CHANNEL);
        if ($this->checkIfNotInArray($channel, array(SHARE_CHANNEL_WECHAT_TIMELINE))) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $share = $this->shareDao->getShare($user->userId, $liveId);
        if ($share) {
            $this->succeed();
            return;
        }
        $ok = $this->shareDao->addShare($user->userId, $liveId, $shareTs, $channel);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

}
