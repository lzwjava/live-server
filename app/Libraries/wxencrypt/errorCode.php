<?php
/**
 * error code 说明.
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */

namespace App\Controllers {

class ErrorCode
{
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;
}

} // end namespace

namespace {
    if (!class_exists('ErrorCode', false) && class_exists('App\Controllers\ErrorCode', false)) {
        class_alias('App\Controllers\ErrorCode', 'ErrorCode');
    }
}
