<?php

use AppModelsShareDao;

namespace App\Controllers;

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

    public function create()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_LIVE_ID,
            KEY_SHARE_TS, KEY_CHANNEL))
        ) {
            return;
        }
        $liveId = $this->request->getPost(KEY_LIVE_ID);
        $shareTs = $this->request->getPost(KEY_SHARE_TS);
        $channel = $this->request->getPost(KEY_CHANNEL);
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
