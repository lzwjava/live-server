<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/9/17
 * Time: 5:48 PM
 */
class AppDao extends BaseDao
{
    function create($name, $userId)
    {
        $data = array(
            KEY_NAME => $name,
            KEY_USER_ID => $userId,
            KEY_ICON_KEY => 'app_icon.png'
        );
        $this->db->insert(TABLE_APPS, $data);
        $appId = $this->db->insert_id();
        return $appId;
    }

    function updateApp($appId, $data)
    {
        $this->db->where(KEY_APP_ID, $appId);
        $this->db->update(TABLE_APPS, $data);
        return $this->db->affected_rows() > 0;
    }

    function createAppImg($imgKey)
    {
        $data = array(KEY_IMG_KEY => $imgKey);
        $this->db->insert(TABLE_APP_IMGS, $data);
        return $this->db->insert_id();
    }

    function getApp($appId)
    {
        $app = $this->getOneFromTable(TABLE_APPS, KEY_APP_ID, $appId);
        $this->assembleApp($app);
        return $app;
    }

    function findMyApps($userId)
    {
        $apps = $this->getListFromTable(TABLE_APPS, KEY_USER_ID, $userId);
        return $apps;
    }

    function assembleApp($app)
    {
        if ($app->iconKey) {
            $app->iconUrl = QINIU_FILE_HOST . '/' . $app->iconKey;
        }
        if ($app->qrcodeKey) {
            $app->qrcodeUrl = QINIU_FILE_HOST . '/' . $app->qrcodeKey;
        }
    }

    function assembleApps($apps)
    {
        foreach ($apps as $app) {
            $this->assembleApp($app);
        }
    }

    function findApps()
    {
        return $this->getListFromTable(TABLE_APPS, '1', '1');
    }

}
