<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 1/9/17
 * Time: 6:29 PM
 */
class AppImgDao extends BaseDao
{
    function addAppImg($appId, $imgKey)
    {
        $data = array(
            KEY_APP_ID => $appId,
            KEY_IMG_KEY => $imgKey
        );
        $this->db->insert(TABLE_APP_IMGS, $data);
        return $this->db->insert_id();
    }

    function removeAppImg($appId, $imgKey)
    {
        $sql = "DELETE FROM app_imgs WHERE $appId=? and $imgKey=?";
        $data = array(KEY_APP_ID => $appId, KEY_IMG_KEY => $imgKey);
        $this->db->udpate(TABLE_APP_IMGS, $sql, $data);
        return $this->db->affected_rows() > 0;
    }

}
