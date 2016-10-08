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
define('ERROR_NOT_ADMIN', 'not_admin');

// error users
define('ERROR_NOT_IN_SESSION', 'not_in_session');
define('ERROR_SMS_WRONG', 'sms_wrong');
define('ERROR_PASSWORD_FORMAT', 'password_format_wrong');
define('ERROR_USERNAME_TAKEN', 'username_taken');
define('ERROR_MOBILE_PHONE_NUMBER_TAKEN', 'phone_number_taken');
define('ERROR_LOGIN_FAILED', 'login_failed');
define('ERROR_QINIU_UPLOAD', 'qiniu_upload');
define('ERROR_USER_BIND', 'user_bind_failed');

// live
define('ERROR_ALIVE_FAIL', 'alive_fail');
define('ERROR_OWNER_CANNOT_ATTEND', 'owner_cannot_attend');
define('ERROR_FIELDS_EMPTY', 'live_fields_empty');
define('ERROR_PLAN_TS_INVALID', 'planTs_invalid');
define('ERROR_EXCEED_MAX_PEOPLE', 'exceed_max_people');
define('ERROR_CREATE_LIVE', 'create_live_failed');
define('ERROR_DETAIL_TOO_SHORT', 'detail_too_short');
define('ERROR_ALREADY_REVIEW', 'already_review');
define('ERROR_LIVE_NOT_WAIT', 'live_not_wait');
define('ERROR_LIVE_NOT_START', 'live_not_start');
define('ERROR_LIVE_NOT_ON', 'live_not_on');
define('ERROR_SPEAKER_INTRO_TOO_SHORT', 'speaker_intro_too_short');

// attendances
define('ERROR_ALREADY_ATTEND', 'already_attend');
define('ERROR_CHARGE_CREATE', 'create_charge_failed');
define('ERROR_NOT_ALLOW_ATTEND', 'not_allow_attend');

// transactions
define('ERROR_BALANCE_INSUFFICIENT', 'balance_insufficient');
define('ERROR_TRANS_FAILED', 'trans_failed');

// qrcodes
define('ERROR_QRCODE_INVALID', 'qrcode_invalid');

// alipay
define('ERROR_PARTNER_OR_SERVICE', 'partner_or_service_wrong');
define('ERROR_SIGN_FAILED', 'sign_failed');
define('ERROR_ALREADY_NOTIFY', 'already_notify');

// leancloud
define('ERROR_LC_CONVERSATION_FAILED', 'lc_conversation_failed');

// wechat
define('ERROR_USER_INFO_FAILED', 'fail_get_user_info');
define('ERROR_GET_ACCESS_TOKEN', 'fail_get_access_token');
define('ERROR_ILLEGAL_REQUEST', 'illegal_request');
define('ERROR_WECHAT_ALREADY_REGISTER', 'wechat_already_register');
define('ERROR_MUST_BIND_WECHAT', 'must_bind_wechat');

if (!function_exists('errorInfos')) {
    function errorInfos()
    {
        return array(
            // common
            ERROR_AT_LEAST_ONE_UPDATE => '请至少提供一个可以修改的信息',
            ERROR_OBJECT_NOT_EXIST => '对象不存在',
            ERROR_SQL_WRONG => '数据库出错',
            ERROR_REDIS_WRONG => 'Redis 出错',
            ERROR_NOT_ALLOW_DO_IT => '您没有相关的权限',
            ERROR_NOT_ADMIN => '此操作只能管理员进行',
            ERROR_PARAMETER_ILLEGAL => '非法的参数',

            // users
            ERROR_USERNAME_TAKEN => '用户名已存在',
            ERROR_NOT_IN_SESSION => '当前没有用户登录',
            ERROR_LOGIN_FAILED => "手机号码不存在或者密码错误",
            ERROR_MOBILE_PHONE_NUMBER_TAKEN => "手机号已被占用",
            ERROR_QINIU_UPLOAD => '七牛上传图片出错',
            ERROR_USER_BIND => '用户绑定出错',

            // lives
            ERROR_AMOUNT_UNIT => 'amount 必须为整数, 单位为分钱. 例如 10 元, amount = 1000.',
            ERROR_AMOUNT_TOO_LITTLE => '门票至少为 1 元',
            ERROR_AMOUNT_TOO_MUCH => '门票最多为 1000 元',
            ERROR_OWNER_CANNOT_ATTEND => '主播自己无法报名',
            ERROR_FIELDS_EMPTY => '需要封面图,主题,详情才能发布',
            ERROR_PLAN_TS_INVALID => '预计的直播时间应该晚于现在',
            ERROR_EXCEED_MAX_PEOPLE => '抱歉,报名已满',
            ERROR_CREATE_LIVE => '创建直播失败',
            ERROR_DETAIL_TOO_SHORT => '直播详情至少要 300 字',
            ERROR_ALREADY_REVIEW => '已经在审核或审核过了',
            ERROR_LIVE_NOT_WAIT => '直播不是报名状态,无法开始',
            ERROR_LIVE_NOT_START => '直播不是进行中状态,无法结束',
            ERROR_LIVE_NOT_ON => '直播并不是测试开始状态,无法恢复到报名中',

            // attendances
            ERROR_ALREADY_ATTEND => '您已报名,无需再次报名.',
            ERROR_CHARGE_CREATE => '创建支付失败',
            ERROR_NOT_ALLOW_ATTEND => '直播尚在编辑之中或者已结束,无法报名',

            // transactions
            ERROR_BALANCE_INSUFFICIENT => '余额不足',
            ERROR_TRANS_FAILED => '交易失败',

            // qrcodes
            ERROR_QRCODE_INVALID => '不符合规范的二维码',

            //alipay
            ERROR_PARTNER_OR_SERVICE => 'partner 或 service 参数错误',
            ERROR_SIGN_FAILED => '签名错误',
            ERROR_ALREADY_NOTIFY => '重复的支付回调',

            // leancloud
            ERROR_LC_CONVERSATION_FAILED => '创建聊天室失败',

            // wechat
            ERROR_GET_ACCESS_TOKEN => '获取令牌失败',
            ERROR_USER_INFO_FAILED => '获取用户信息失败',
            ERROR_ILLEGAL_REQUEST => '非法的请求',
            ERROR_WECHAT_ALREADY_REGISTER => '微信早已注册,请退出并重新进入',
            ERROR_MUST_BIND_WECHAT => '必须先绑定微信'
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
define('KEY_CONVERSATION_ID', 'conversationId');
define('KEY_COVER_URL', 'coverUrl');
define('KEY_PREVIEW_URL', 'previewUrl');
define('KEY_AMOUNT', 'amount');
define('KEY_MAX_PEOPLE', 'maxPeople');
define('KEY_SPEAKER_INTRO', 'speakerIntro');
define('KEY_PLAN_TS', 'planTs');
define('KEY_BEGIN_TS', 'beginTs');
define('KEY_END_TS', 'endTs');
define('KEY_OWNER_ID', 'ownerId');
define('KEY_DETAIL', 'detail');

define('LIVE_STATUS_PREPARE', 1);
define('LIVE_STATUS_REVIEW', 5);
define('LIVE_STATUS_WAIT', 10);
define('LIVE_STATUS_ON', 20);
define('LIVE_STATUS_OFF', 30);

// sms
define('SMS_TEMPLATE', 'template');
define('SMS_NAME', 'name');
define('SMS_OPEN_APP_WORDS', 'openAppWords');
define('SMS_INTRO', 'intro');
define('SMS_TIME', 'time');
define('SMS_OWNER_NAME', 'ownerName');
define('SMS_LINK', 'link');

// users
define('TABLE_USERS', 'users');
define('KEY_MOBILE_PHONE_NUMBER', 'mobilePhoneNumber');
define('KEY_AVATAR_URL', 'avatarUrl');
define('KEY_SESSION_TOKEN', 'sessionToken');
define('KEY_SESSION_TOKEN_CREATED', 'sessionTokenCreated');
define('KEY_PASSWORD', 'password');
define('KEY_USERNAME', 'username');
define('KEY_USER_ID', 'userId');
define('KEY_SMS_CODE', 'smsCode');

// charges
define('TABLE_CHARGES', 'charges');
define('KEY_CHARGE_ID', 'chargeId');
define('KEY_ORDER_NO', 'orderNo');
define('KEY_PAID', 'paid');
define('KEY_CREATOR', 'creator');
define('KEY_CREATOR_IP', 'creatorIP');
define('KEY_META_DATA', 'metaData');
define('KEY_CHANNEL', 'channel');
define('CHANNEL_WECHAT_H5', 'wechat_h5');
define('CHANNEL_ALIPAY_APP', 'alipay_app');

// attendances
define('TABLE_ATTENDANCES', 'attendances');
define('KEY_ATTENDANCE_ID', 'attendanceId');
define('KEY_ATTENDANCE_COUNT', 'attendanceCount');
define('KEY_NOTIFIED', 'notified');

// transactions
define('TABLE_TRANSACTIONS', 'transactions');
define('KEY_TRANSACTION_ID', 'transactionId');
define('KEY_OLD_BALANCE', 'oldBalance');
define('KEY_RELATED_ID', 'relatedId');
define('KEY_TYPE', 'type');
define('KEY_REMARK', 'remark');

// accounts
define('TABLE_ACCOUNTS', 'accounts');
define('KEY_ACCOUNT_ID', 'accountId');
define('KEY_BALANCE', 'balance');

define('TRANS_TYPE_RECHARGE', 1);
define('TRANS_TYPE_PAY', 2);
define('TRANS_TYPE_INCOME', 3);

define('REMARK_ALIPAY', '支付宝充值');
define('REMARK_WECHAT', '微信充值');
define('REMARK_PAY', '参加%s的直播');
define('REMARK_INCOME_LIVE', '%s报名直播');

// qrcodes
define('TABLE_SCANNED_QRCODES', 'scanned_qrcodes');
define('KEY_QRCODE_ID', 'qrcodeId');
define('KEY_CODE', 'code');
define('KEY_SCANNED', 'scanned');
define('KEY_DATA', 'data');
define('TYPE_CREATE', 0);
define('TYPE_WATCH', 1);

// sns_users
define('TABLE_SNS_USERS', 'sns_users');
define('KEY_SNS_USER_ID', 'snsUserId');
define('KEY_OPEN_ID', 'openId');
define('KEY_PLATFORM', 'platform');
define('PLATFORM_WECHAT', 'wechat');

// states
define('TABLE_STATES', 'states');
define('KEY_HASH', 'hash');
define('KEY_STATE', 'state');

// shares
define('TABLE_SHARES', 'shares');
define('KEY_SHARE_TS', 'shareTs');
define('SHARE_CHANNEL_WECHAT_TIMELINE', 'wechat_timeline');

// cookie
define('KEY_COOKIE_TOKEN', 'SessionToken');
define('COOKIE_VID', 'vid');
define('KEY_SESSION_HEADER', 'X-Session');

define('LC_TEST_APP_ID', 'YY3S7uNlnXUgX48BHTJlJx4i-gzGzoHsz');
define('LC_TEST_APP_KEY', 'h9zCqcPSi7nDgQTQE6YsOT0z');

define('LC_PROD_APP_ID', 's83aTX5nigX1KYu9fjaBTxIa-gzGzoHsz');
define('LC_PROD_APP_KEY', 'V4FPFLSmSeO1HaIwPVyhO9P3');

// lc
if (ENVIRONMENT == 'development') {
    define('LC_APP_ID', LC_TEST_APP_ID);
    define('LC_APP_KEY', LC_TEST_APP_KEY);
} else {
    define('LC_APP_ID', LC_PROD_APP_ID);
    define('LC_APP_KEY', LC_PROD_APP_KEY);
}


define('RTMP_URL_PREFIX', "rtmp://quzhiboapp.com/live/");
define('ALIPAY_NOTIFY_URL', 'http://api.quzhiboapp.com/rewards/notify');

define('WECHAT_APP_ID', 'wx7b5f277707699557');
define('WECHAT_APP_SECRET', '3d85c980817fd92eac4530b3c0ce667a');
define('WECHAT_MCHID', '1387703002');


define('QINIU_FILE_HOST', 'http://i.quzhiboapp.com');
define('QINIU_ACCESS_KEY', '-ON85H3cEMUaCuj8UFpLELeEunEAqslrqYqLbn9g');
define('QINIU_SECRET_KEY', 'X-oHOYDinDEhNk5nr74O1rKDvkmPq0ZQwEZfFt6x');

define('KEY_URL', 'url');

// live hooks
define('KEY_STREAM', 'stream');
define('KEY_ACTION', 'action');

define('KEY_TEXT', 'text');


if (!function_exists('specialPhones')) {
    function specialPhones()
    {
        return array('817015130624', '13942341609', '18928980893', '13189049707', '12167851210', '13091105688',
            '85267142420', '0978632991');
    }
}
