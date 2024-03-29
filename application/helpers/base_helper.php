<?php
/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 15/11/30
 * Time: 下午3:44
 */

if (!function_exists('dateWithMs')) {
    function dateWithMs()
    {
        list ($t1, $t2) = explode(' ', microtime());
        $date = new DateTime ();
        $date->setTimestamp($t2);

        return $date->format("Y-m-d H:i:s") . substr($t1, 1, 7);
    }
}

if (!function_exists('uuid')) {
    function uuid($len = null)
    {
        $uuid = md5(uniqid());
        if ($len == null) {
            return $uuid;
        } else {
            return substr($uuid, 0, $len);
        }
    }
}

if (!function_exists('setCookieForever')) {
    function setCookieForever($name, $value)
    {
        setcookie($name, $value, time() + 3600 * 24 * 365 * 20, '/');
    }
}

if (!function_exists('tokenChars')) {
    function tokenChars()
    {
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        return $codeAlphabet;
    }
}

if (!function_exists('getToken')) {

    function getToken($length = 16)
    {
        $token = "";
        $codeAlphabet = tokenChars();
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[rand(0, $max)];
        }
        return $token;
    }
}

if (!function_exists('dbField')) {
    function dbField($table, $key)
    {
        return $table . '.' . $key;
    }
}

if (!function_exists('deleteCookie')) {
    function deleteCookie($name)
    {
        setcookie($name, "", time() - 10000, "/");
    }
}

if (!function_exists('logInfo')) {
    function logInfo($info)
    {
        if (isDebug()) {
            error_log($info);
        } else {
            log_message('error', $info);
        }
    }
}

if (!function_exists('logError')) {
    function logError($error)
    {
        if (isDebug()) {
            error_log($error);
        } else {
            log_message('error', $error);
        }
    }
}

if (!function_exists('amountToYuan')) {
    function amountToYuan($amount)
    {
        return $amount / 100;
    }
}

if (!function_exists('moneyFormat')) {
    function moneyFormat($amount)
    {
        return number_format($amount / 100.0, 2, '.', '');
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat($dateStr)
    {
        $dateTime = date_create($dateStr, new DateTimeZone('Asia/Shanghai'));
        date_format($dateTime, 'Y-m-d H:i');
    }
}


if (!function_exists('extractFields')) {
    function extractFields($object, $fields, $prefix = null)
    {
        $newObj = new StdClass();
        $hasSet = false;
        foreach ($fields as $field) {
            if (isset($object, $field)) {
                if ($object->$field !== null) {
                    $hasSet = true;
                    $newField = $field;
                    if ($prefix) {
                        if (substr($field, 0, strlen($prefix)) == $prefix) {
                            $newField = substr($field, strlen($prefix));
                        }
                    }
                    $newObj->$newField = $object->$field;
                }
                unset($object->$field);
            }
        }
        if ($hasSet) {
            return $newObj;
        } else {
            return null;
        }
    }
}

if (!function_exists('truncate')) {
    function truncate($string, $maxLength = 12)
    {
        if ($string == null) {
            return $string;
        }
        if (strlen($string) > $maxLength) {
            return substr($string, 0, $maxLength);
        } else {
            return $string;
        }
    }
}

if (!function_exists('genOrderNo')) {
    function genOrderNo()
    {
        return getToken(16);
    }
}

if (!function_exists('isTimeBeforeNow')) {
    function isTimeBeforeNow($time)
    {
        $dateTime = date_create($time, new DateTimeZone('Asia/Shanghai'));
        $now = date_create('now');
        $diff = date_diff($dateTime, $now);
        return $diff->invert == 0;
    }
}


if (!function_exists("liveSort")) {
    function liveSort($liveA, $liveB)
    {
        logInfo("encoded:" . json_encode($liveA));
        if ($liveA->status != $liveB->status) {
            return $liveA->status - $liveB->status;
        } else {
            $liveADate = date_create($liveA->created, new DateTimeZone('Asia/Shanghai'));
            $liveBDate = date_create($liveB->created, new DateTimeZone('Asia/Shanghai'));
            $diff = date_diff($liveADate, $liveBDate);
            if ($diff->invert == 0) {
                return 1;
            } else {
                return -1;
            }
        }
    }
}


if (!function_exists("filterHost")) {
    /**
     * 应更换https的需要,剔除qiniu路径中的协议头
     * @param $url
     * @return string 替换后的字符串
     */
    function filterHost($url)
    {
        return str_replace(QINIU_FILE_HOST, QINIU_FILE_HOST_WITHOUT_SCHEME, $url);
    }
}

if (!function_exists('fixHttpsUrl')) {
    function fixHttpsUrl($url)
    {
//        return $url;
        return str_replace('https://', 'http://', $url);
    }
}
