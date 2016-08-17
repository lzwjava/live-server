<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 8/17/16
 * Time: 3:12 PM
 */
class QrcodeDao extends BaseDao
{
    function addQrcode($code, $userId)
    {
        $data = array(
            KEY_CODE => $code,
            KEY_USER_ID => $userId
        );
        $this->db->insert(TABLE_SCANNED_QRCODES, $data);
        return $this->db->insert_id();
    }

    private function fields()
    {
        return array(KEY_QRCODE_ID, KEY_CODE, KEY_USER_ID, KEY_CREATED);
    }

    private function publicFields($prefix = TABLE_SCANNED_QRCODES, $alias = false)
    {
        return $this->mergeFields($this->fields(), $prefix, $alias);
    }

    function getQrcode($code)
    {
        $fields = $this->publicFields();
        return $this->getOneFromTable(TABLE_SCANNED_QRCODES, KEY_CODE, $code, $fields);
    }

}
