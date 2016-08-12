<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE') OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ') OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESCTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS') OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

define('REQ_OK', 'success');


// error common
define('ERROR_MISS_PARAMETERS', 'missing_parameters');
define('ERROR_AT_LEAST_ONE_UPDATE', 'at_least_one_update');
define('ERROR_OBJECT_NOT_EXIST', 'object_not_exists');
define('ERROR_UNKNOWN_TYPE', 'unknown_type');
define('ERROR_NOT_ALLOW_DO_IT', 'not_allow_do_it');
define('ERROR_PARAMETER_ILLEGAL', 'parameter_illegal');
define('ERROR_SQL_WRONG', 'sql_wrong');
define('ERROR_REDIS_WRONG', 'redis_wrong');
define('ERROR_AMOUNT_UNIT', 'amount_unit');
define('ERROR_AMOUNT_TOO_LITTLE', 'amount_too_little');
define('ERROR_AMOUNT_TOO_MUCH', 'amount_too_much');

// error users
define('ERROR_NOT_IN_SESSION', 'not_in_session');
define('ERROR_SMS_WRONG', 'sms_wrong');
define('ERROR_PASSWORD_FORMAT', 'password_format_wrong');
define('ERROR_USERNAME_TAKEN', 'username_taken');
define('ERROR_MOBILE_PHONE_NUMBER_TAKEN', 'phone_number_taken');
define('ERROR_LOGIN_FAILED', 'login_failed');

// live
define('ERROR_ALIVE_FAIL', 'alive_fail');
define('ERROR_OWNER_CANNOT_ATTEND', 'owner_cannot_attend');
define('ERROR_FIELDS_EMPTY', 'live_fields_empty');
define('ERROR_PLAN_TS_INVALID', 'plan_ts_invalid');


// attendances
define('ERROR_ALREADY_ATTEND', 'already_attend');
define('ERROR_PINGPP_CHARGE', 'pingpp_failed');
define('ERROR_NOT_ALLOW_ATTEND', 'not_allow_attend');


if (!function_exists('errorInfos')) {
    function errorInfos()
    {
        return array(
            // common
            ERROR_AT_LEAST_ONE_UPDATE => '请至少提供一个可以修改的信息',
            ERROR_OBJECT_NOT_EXIST => '对象不存在',
            ERROR_SQL_WRONG => '数据库出错',
            ERROR_REDIS_WRONG => 'Redis 出错',

            // users
            ERROR_USERNAME_TAKEN => '用户名已存在',
            ERROR_NOT_IN_SESSION => '当前没有用户登录',
            ERROR_LOGIN_FAILED => "手机号码不存在或者密码错误",
            ERROR_MOBILE_PHONE_NUMBER_TAKEN => "手机号已被占用",

            // lives
            ERROR_AMOUNT_UNIT => 'amount 必须为整数, 单位为分钱. 例如 10 元, amount = 1000.',
            ERROR_AMOUNT_TOO_LITTLE => '门票至少为 1 元',
            ERROR_AMOUNT_TOO_MUCH => '门票最多为 1000 元',
            ERROR_OWNER_CANNOT_ATTEND => '主播自己无法报名',
            ERROR_FIELDS_EMPTY => '需要封面图,主题,详情才能发布',
            ERROR_PLAN_TS_INVALID => '预计的直播时间应该晚于现在',

            // attendances
            ERROR_ALREADY_ATTEND => '您已报名,无需再次报名.',
            ERROR_PINGPP_CHARGE => '创建支付失败',
            ERROR_NOT_ALLOW_ATTEND => '直播尚在编辑之中或者已结束,无法报名'
        );
    }

}

// pay
define('LEAST_COMMON_PAY', 100);
define('MAX_COMMON_PAY', 100 * 1000);

// common
define('KEY_SKIP', 'skip');
define('KEY_LIMIT', 'limit');
define('KEY_CREATED', 'created');
define('KEY_UPDATED', 'updated');

// live
define('TABLE_LIVES', 'lives');
define('KEY_LIVE_ID', 'liveId');
define('KEY_SUBJECT', 'subject');
define('KEY_RTMP_KEY', 'rtmpKey');
define('KEY_STATUS', 'status');
define('KEY_COVER_URL', 'coverUrl');
define('KEY_AMOUNT', 'amount');
define('KEY_PLAN_TS', 'plan_ts');
define('KEY_BEGIN_TS', 'begin_ts');
define('KEY_END_TS', 'end_ts');
define('KEY_OWNER_ID', 'ownerId');
define('KEY_DETAIL', 'detail');

define('LIVE_STATUS_PREPARE', 1);
define('LIVE_STATUS_WAIT', 2);
define('LIVE_STATUS_ON', 3);
define('LIVE_STATUS_OFF', 4);

define('SMS_TEMPLATE', 'template');
define('KEY_SMS_CODE', 'smsCode');

// users
define('TABLE_USERS', 'users');
define('KEY_MOBILE_PHONE_NUMBER', 'mobilePhoneNumber');
define('KEY_AVATAR_URL', 'avatarUrl');
define('KEY_SESSION_TOKEN', 'sessionToken');
define('KEY_SESSION_TOKEN_CREATED', 'sessionTokenCreated');
define('KEY_PASSWORD', 'password');
define('KEY_USERNAME', 'username');
define('KEY_USER_ID', 'userId');

// charges
define('TABLE_CHARGES', 'charges');
define('KEY_CHARGE_ID', 'chargeId');
define('KEY_ORDER_NO', 'orderNo');
define('KEY_PAID', 'paid');
define('KEY_CREATOR', 'creator');
define('KEY_CREATOR_IP', 'creatorIP');

// attendances
define('TABLE_ATTENDANCES', 'attendances');
define('KEY_ATTENDANCE_ID', 'attendanceId');
define('KEY_ATTENDANCE_COUNT', 'attendanceCount');

// cookie
define('KEY_COOKIE_TOKEN', 'SessionToken');
define('COOKIE_VID', 'vid');
define('KEY_SESSION_HEADER', 'X-Session');

// lc
define('LC_APP_ID', 's83aTX5nigX1KYu9fjaBTxIa-gzGzoHsz');
define('LC_APP_KEY', 'V4FPFLSmSeO1HaIwPVyhO9P3');
