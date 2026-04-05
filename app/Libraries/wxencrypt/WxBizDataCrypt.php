<?php
/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */

namespace App\Controllers {

include_once __DIR__ . "/pkcs7Encoder.php";
include_once __DIR__ . "/errorCode.php";

class WXBizDataCrypt
{
    private $appid;
    private $sessionKey;

    function __construct($appid, $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->appid = $appid;
    }

    public function decryptData($encryptedData, $iv, &$data)
    {
        if (strlen($this->sessionKey) != 24) {
            return \App\Controllers\ErrorCode::$IllegalAesKey;
        }
        $aesKey = base64_decode($this->sessionKey);

        if (strlen($iv) != 24) {
            return \App\Controllers\ErrorCode::$IllegalIv;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $pc = new \App\Controllers\Prpcrypt($aesKey);
        $result = $pc->decrypt($aesCipher, $aesIV);

        if ($result[0] != 0) {
            return $result[0];
        }

        $dataObj = json_decode($result[1]);
        if ($dataObj == NULL) {
            return \App\Controllers\ErrorCode::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return \App\Controllers\ErrorCode::$IllegalBuffer;
        }
        $data = $result[1];
        return \App\Controllers\ErrorCode::$OK;
    }
}

} // end namespace

// Global namespace: allow WxBizDataCrypt (global) via require_once from CI3 code
namespace {
    if (!class_exists('WXBizDataCrypt', false)) {
        class_alias('App\Controllers\WXBizDataCrypt', 'WXBizDataCrypt');
    }
}
