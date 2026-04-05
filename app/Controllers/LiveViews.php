<?php

use AppModelsViewDao;
use AppModelsLiveViewDao;

namespace App\Controllers;

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/5/16
 * Time: 11:44 AM
 */
class LiveViews extends BaseController
{
    public $liveViewDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(LiveViewDao::class);
        $this->liveViewDao = new LiveViewDao();
    }

    public function create()
    {
        if ($this->checkIfParamsNotExist($this->request->getPost(), array(KEY_LIVE_ID,
            KEY_PLATFORM, KEY_LIVE_STATUS))
        ) {
            return;
        }
        $liveId = $this->request->getPost(KEY_LIVE_ID);
        $platform = $this->request->getPost(KEY_PLATFORM);
        $liveStatus = $this->toNumber($this->request->getPost(KEY_LIVE_STATUS));
        if ($this->checkIfNotInArray($platform, viewPlatformSet())
        ) {
            return;
        }
        if ($this->checkIfNotInArray($liveStatus, liveStatusSet())) {
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        $id = $this->liveViewDao->addLiveView($user->userId, $liveId, $platform, $liveStatus);
        if (!$id) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed(array(KEY_LIVE_VIEW_ID => $id));
    }

    public function end($liveViewId)
    {
        $this->liveViewDao->endLiveView($liveViewId);
        $this->succeed();
    }
}
