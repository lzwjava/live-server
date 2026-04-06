<?php
/**
 * Base helper functions — migrated from CI3 base_helper.php
 */

if (!function_exists('dateWithMs')) {
    function dateWithMs()
    {
        list($t1, $t2) = explode(' ', microtime());
        $date = new DateTime();
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
            $token .= $codeAlphabet[random_int(0, $max)];
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
        log_message('info', is_string($info) ? $info : json_encode($info));
    }
}

if (!function_exists('logError')) {
    function logError($error)
    {
        log_message('error', is_string($error) ? $error : json_encode($error));
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
        return date_format($dateTime, 'Y-m-d H:i');
    }
}

if (!function_exists('extractFields')) {
    function extractFields($object, $fields, $prefix = null)
    {
        $newObj = new \stdClass();
        $hasSet = false;
        foreach ($fields as $field) {
            if (isset($object->$field)) {
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

if (!function_exists('liveSort')) {
    function liveSort($liveA, $liveB)
    {
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

if (!function_exists('filterHost')) {
    function filterHost($url)
    {
        return str_replace(QINIU_FILE_HOST, QINIU_FILE_HOST_WITHOUT_SCHEME, $url);
    }
}

if (!function_exists('fixHttpsUrl')) {
    function fixHttpsUrl($url)
    {
        return str_replace('https://', 'http://', $url);
    }
}

if (!function_exists('isDebug')) {
    function isDebug()
    {
        return ENVIRONMENT == 'development';
    }
}

if (!function_exists('specialPhones')) {
    function specialPhones()
    {
        return array('817015130624', '13942341609', '18928980893', '13189049707', '12167851210', '13091105688',
            '85267142420', '0978632991', '4128889028', '0433567897', '64220361907', '61422231593', '61415093761',
            '18101070110', '07469289888', '2347169053', '033650122672', '6598586126', '16124811322', '2672546088',
            '18621360072', '61414143183', '8860921519977', '61426370123', '886919544103', '16047715605',
            '15129342293', '13888888888', '15718810378', '17034342233', '13121222685', '2269780688', '13144356901');
    }
}

if (!function_exists('session_unset')) {
    function session_unset($key = null)
    {
        if ($key === null) {
            \Config\Services::session()->destroy();
        } else {
            \Config\Services::session()->remove($key);
        }
    }
}

if (!function_exists('channelSet')) {
    function channelSet()
    {
        return array(CHANNEL_WECHAT_H5, CHANNEL_WECHAT_QRCODE, CHANNEL_ALIPAY_APP,
            CHANNEL_WECHAT_APP, CHANNEL_APPLE_IAP);
    }
}

if (!function_exists('errorInfos')) {
    function errorInfos()
    {
        return array(
            ERROR_AT_LEAST_ONE_UPDATE => '请至少提供一个可以修改的信息',
            ERROR_OBJECT_NOT_EXIST => '对象不存在',
            ERROR_SQL_WRONG => '数据库出错',
            ERROR_REDIS_WRONG => 'Redis 出错',
            ERROR_NOT_ALLOW_DO_IT => '您没有相关的权限',
            ERROR_NOT_ADMIN => '此操作只能管理员进行',
            ERROR_PARAMETER_ILLEGAL => '非法的参数',
            ERROR_INSERT_SQL_WRONG => '插入数据失败',
            ERROR_USERNAME_TAKEN => '用户名已存在',
            ERROR_NOT_IN_SESSION => '当前没有用户登录',
            ERROR_LOGIN_FAILED => '手机号码不存在或者密码错误',
            ERROR_MOBILE_PHONE_NUMBER_TAKEN => '手机号已被占用',
            ERROR_QINIU_UPLOAD => '七牛上传图片出错',
            ERROR_USER_BIND => '用户绑定出错',
            ERROR_NOT_ALLOW_APP_REGISTER => '请从平方根平台公众号注册,iOS 上注册暂时会影响微信版的使用',
            ERROR_NOT_ALLOW_APP_LOGIN => '请使用平方根平台公众号,iOS会崩溃',
            ERROR_ALREADY_BIND_PHONE => '已经绑定过手机号码了',
            ERROR_NOT_ATTEND_LIVE => '还没参加过直播,没有分类',
            ERROR_NOT_LIVE_TOPIC => '直播分类为空',
            ERROR_AMOUNT_UNIT => 'amount 必须为整数, 单位为分钱. 例如 10 元, amount = 1000.',
            ERROR_AMOUNT_TOO_LITTLE => '门票至少为 1 分钱',
            ERROR_AMOUNT_TOO_MUCH => '门票最多为 1000 元',
            ERROR_OWNER_CANNOT_ATTEND => '主播自己无法报名',
            ERROR_FIELDS_EMPTY => '需要封面图,主题,详情才能发布',
            ERROR_PLAN_TS_INVALID => '预计的直播时间应该晚于现在',
            ERROR_EXCEED_MAX_PEOPLE => '抱歉,报名已满',
            ERROR_CREATE_LIVE => '创建直播失败',
            ERROR_DETAIL_TOO_SHORT => '直播详情至少要 100 字',
            ERROR_ALREADY_REVIEW => '已经在审核或审核过了',
            ERROR_LIVE_NOT_WAIT => '直播不是报名状态,无法开始',
            ERROR_LIVE_NOT_START => '直播不是进行中状态,无法结束',
            ERROR_LIVE_NOT_ON => '直播并不是测试开始状态,无法恢复到报名中',
            ERROR_LIVE_NOT_TRANSCODE => '直播没有在转码状态,无法结束',
            ERROR_PLAYBACK_FAIL => '生成回放失败',
            ERROR_LIVE_NOT_REVIEW => '直播不在审核状态,无法完成该操作',
            ERROR_LIVE_BEGIN_EARLY => '直播不能开始的太早',
            ERROR_ALREADY_ATTEND => '您已报名,无需再次报名.',
            ERROR_CHARGE_CREATE => '创建支付失败',
            ERROR_NOT_ALLOW_ATTEND => '直播尚在编辑之中或者已结束,无法报名',
            ERROR_SMS_WRONG => '短信验证码错误',
            ERROR_PASSWORD_FORMAT => '密码格式错误',
            ERROR_VERIFY_RECEIPT => '验证收据失败',
            ERROR_WITHDRAW_AMOUNT_TOO_LITTLE => '提现金额不足',
            ERROR_WITHDRAW_ALREADY_DONE => '已经提现过了',
            ERROR_MISS_PARAMETERS => '缺少参数',
        );
    }
}
