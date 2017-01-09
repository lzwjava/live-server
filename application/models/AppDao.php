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
            KEY_USER_ID => $userId
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
        return $this->getOneFromTable(TABLE_APPS, KEY_APP_ID, $appId);
    }

    function findMyApps($userId)
    {
        return $this->getListFromTable(TABLE_APPS, KEY_USER_ID, $userId);
    }

    function findApps()
    {
        return $this->getListFromTable(TABLE_APPS, '1', '1');
    }

}
