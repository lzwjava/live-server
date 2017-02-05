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
define('ERROR_INSERT_SQL_WRONG', 'insert_sql_wrong');
define('ERROR_REDIS_WRONG', 'redis_wrong');
define('ERROR_AMOUNT_UNIT', 'amount_unit');
define('ERROR_AMOUNT_TOO_LITTLE', 'amount_too_little');
define('ERROR_AMOUNT_TOO_MUCH', 'amount_too_much');
define('ERROR_NOT_ADMIN', 'not_admin');
define('ERROR_WECHAT', 'wechat_error');

// error users
define('ERROR_NOT_IN_SESSION', 'not_in_session');
define('ERROR_SMS_WRONG', 'sms_wrong');
define('ERROR_PASSWORD_FORMAT', 'password_format_wrong');
define('ERROR_USERNAME_TAKEN', 'username_taken');
define('ERROR_MOBILE_PHONE_NUMBER_TAKEN', 'phone_number_taken');
define('ERROR_LOGIN_FAILED', 'login_failed');
define('ERROR_QINIU_UPLOAD', 'qiniu_upload');
define('ERROR_USER_BIND', 'user_bind_failed');
define('ERROR_NOT_ALLOW_APP_REGISTER', 'not_allow_app_register');
define('ERROR_NOT_ALLOW_APP_LOGIN', 'not_allow_app_login');
define('ERROR_ALREADY_BIND_PHONE', 'already_bind_phone');
define('ERROR_MUST_BIND_PHONE', 'must_bind_phone');

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
define('ERROR_LIVE_NOT_TRANSCODE', 'live_not_transcode');
define('ERROR_LIVE_NOT_START', 'live_not_start');
define('ERROR_LIVE_NOT_ON', 'live_not_on');
define('ERROR_SPEAKER_INTRO_TOO_SHORT', 'speaker_intro_too_short');
define('ERROR_PLAYBACK_FAIL', 'playback_fail');

// attendances
define('ERROR_ALREADY_ATTEND', 'already_attend');
define('ERROR_CHARGE_CREATE', 'create_charge_failed');
define('ERROR_NOT_ALLOW_ATTEND', 'not_allow_attend');

// transactions
define('ERROR_BALANCE_INSUFFICIENT', 'balance_insufficient');
define('ERROR_TRANS_FAILED', 'trans_failed');

// qrcodes
define('ERROR_QRCODE_INVALID', 'qrcode_invalid');
define('ERROR_QRCODE_DUPLICATE', 'qrcode_duplicate');

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
define('ERROR_GET_UNION_ID', 'fail_get_union_id');
define('ERROR_BIND_UNION_ID', 'fail_bind_union_id');
define('ERROR_UNION_ID_EMPTY', 'union_id_empty');
define('ERROR_UNION_ID_USER_NOT_EXISTS', 'union_id_user_not_exists');
define('ERROR_SNS_USER_NOT_EXISTS', 'sns_user_not_exists');
define('ERROR_SNS_USER_ID_EMPTY', 'sns_user_user_id_empty');
define('ERROR_GET_USER_INFO', 'fail_get_user_info');
define('ERROR_BIND_UNION_ID_TO_USER', 'fail_bind_union_id_to_user');
define('ERROR_WECHAT_ALREADY_BIND', 'wechat_already_bind');
define('ERROR_BIND_WECHAT_FAILED', 'fail_bind_wechat');

// wechat app
define('ERROR_SESSION_KEY_NOT_EXISTS', 'session_key_not_exists');
define('ERROR_WX_ENCRYPT', 'wx_encrypt');
define('ERROR_WX_SIGN', 'wx_sign');
define('ERROR_WX_SESSION_EXPIRE', 'wx_session_expire');
define('ERROR_WX_SESSION_LEN', 'wx_session_len');
define('ERROR_WX_SNS_USER_NOT_EXISTS', 'sns_user_not_exists');

// videos
define('ERROR_VIDEOS_NOT_GEN', 'video_not_gen');
define('ERROR_FAIL_HANDLE_VIDEO', 'fail_handle_video');
define('ERROR_CONVERT_VIDEO', 'fail_convert_video');
define('ERROR_MERGE_VIDEO', 'fail_merge_video');
define('ERROR_SCP_FAIL', 'fail_scp');

// rewards
define('ERROR_REWARD_TOO_LITTLE', 'reward_too_little');
define('ERROR_REWARD_TOO_MUCH', 'reward_too_much');
define('ERROR_REWARD_YOURSELF', 'reward_yourself');
define('ERROR_NOT_ATTEND', 'not_attend');

// staffs
define('ERROR_ALREADY_STAFF', 'already_staff');

// applications
define('ERROR_SOCIAL_ACCOUNT_ELN', 'social_account_len');
define('ERROR_INTRODUCTION_LEN', 'introduction_len');
define('ERROR_REVIEW_REMARK_LEN', 'review_remark_len');
define('ERROR_ALREADY_APPLY', 'already_apply');
define('ERROR_WECHAT_NUM_FORMAT', 'wechat_num_format');

define('ERROR_PACKET_TOO_LITTLE', 'packet_too_little');
define('ERROR_PACKET_TOO_MUCH', 'packet_too_much');
define('ERROR_PACKET_NONE', 'packet_none');
define('ERROR_ALREADY_GRAB', 'packet_already_grab');
define('ERROR_PACKET_SEND', 'packet_send');
define('ERROR_PACKET_AT_LEAST', 'packet_at_least');

// accounts
define('ERROR_VERIFY_RECEIPT', 'verify_receipt_wrong');

// withdraws
define('ERROR_MUST_SUBSCRIBE', 'must_subscribe');
define('ERROR_EXCEED_BALANCE', 'exceed_balance');
define('ERROR_WITHDRAW_AMOUNT_TOO_LITTLE', 'withdraw_amount_too_little');
define('ERROR_HAVE_WAIT_WITHDRAW', 'have_wait_withdraw');
define('ERROR_HAVE_WAIT_LIVE', 'have_wait_live');

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
            ERROR_INSERT_SQL_WRONG => '插入数据失败',

            // users
            ERROR_USERNAME_TAKEN => '用户名已存在',
            ERROR_NOT_IN_SESSION => '当前没有用户登录',
            ERROR_LOGIN_FAILED => "手机号码不存在或者密码错误",
            ERROR_MOBILE_PHONE_NUMBER_TAKEN => "手机号已被占用",
            ERROR_QINIU_UPLOAD => '七牛上传图片出错',
            ERROR_USER_BIND => '用户绑定出错',
            ERROR_NOT_ALLOW_APP_REGISTER => '请从平方根平台公众号注册,iOS 上注册暂时会影响微信版的使用',
            ERROR_NOT_ALLOW_APP_LOGIN => '请使用平方根平台公众号,iOS会崩溃',
            ERROR_ALREADY_BIND_PHONE => '已经绑定过手机号码了',

            // lives
            ERROR_AMOUNT_UNIT => 'amount 必须为整数, 单位为分钱. 例如 10 元, amount = 1000.',
            ERROR_AMOUNT_TOO_LITTLE => '门票至少为 1 分钱',
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
            ERROR_LIVE_NOT_TRANSCODE => '直播没有在转码状态,无法结束',
            ERROR_PLAYBACK_FAIL => '生成回放失败',

            // attendances
            ERROR_ALREADY_ATTEND => '您已报名,无需再次报名.',
            ERROR_CHARGE_CREATE => '创建支付失败',
            ERROR_NOT_ALLOW_ATTEND => '直播尚在编辑之中或者已结束,无法报名',

            // transactions
            ERROR_BALANCE_INSUFFICIENT => '余额不足',
            ERROR_TRANS_FAILED => '交易失败',

            // qrcodes
            ERROR_QRCODE_INVALID => '不符合规范的二维码',
            ERROR_QRCODE_DUPLICATE => '此二维码已使用, 请刷新电脑网页的二维码',

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
            ERROR_MUST_BIND_WECHAT => '必须先绑定微信',
            ERROR_GET_UNION_ID => '获取微信统一ID失败',
            ERROR_BIND_UNION_ID => '绑定微信统一ID失败',
            ERROR_UNION_ID_EMPTY => '微信统一ID不能为空,请退出并重新进入',
            ERROR_UNION_ID_USER_NOT_EXISTS => '微信统一ID对应的用户不存在',
            ERROR_SNS_USER_NOT_EXISTS => '还未注册趣直播, 请关注平方根平台公众号, 登录注册一下',
            ERROR_GET_USER_INFO => '获取微信用户信息失败',
            ERROR_BIND_UNION_ID_TO_USER => '无法绑定微信统一ID到用户',
            ERROR_WECHAT_ALREADY_BIND => '微信已经绑定过了,无需重复绑定',
            ERROR_BIND_WECHAT_FAILED => '绑定微信与手机号失败',
            ERROR_SNS_USER_ID_EMPTY => '还没绑定手机, 请关注平方根平台公众号, 进入直播绑定一下',

            // wechat app
            ERROR_SESSION_KEY_NOT_EXISTS => '登录已过期或相应的登录信息不存在',
            ERROR_WX_ENCRYPT => '微信数据加解密失败',
            ERROR_WX_SIGN => '数据校验失败',
            ERROR_WX_SESSION_EXPIRE => '微信登录过期,请重新登录',
            ERROR_WX_SESSION_LEN => '登录令牌长度错误',
            ERROR_WX_SNS_USER_NOT_EXISTS => '还没注册微信用户',

            // videos
            ERROR_VIDEOS_NOT_GEN => '回放视频还没有生成',
            ERROR_FAIL_HANDLE_VIDEO => '处理视频失败',
            ERROR_CONVERT_VIDEO => '转码视频失败',
            ERROR_MERGE_VIDEO => '视频合并失败',
            ERROR_SCP_FAIL => '视频传输失败',

            // rewards
            ERROR_REWARD_TOO_LITTLE => '打赏最低为1元',
            ERROR_REWARD_TOO_MUCH => '打赏最高为1000元',
            ERROR_REWARD_YOURSELF => '不能打赏自己',
            ERROR_NOT_ATTEND => '尚未参与该场直播',

            // staffs
            ERROR_ALREADY_STAFF => '您早已绑定账号',

            // applications
            ERROR_SOCIAL_ACCOUNT_ELN => '社交账号介绍不能超过 200 个字符',
            ERROR_INTRODUCTION_LEN => '个人介绍不能超过 500 个字符',
            ERROR_REVIEW_REMARK_LEN => '审核评语长度不能超过 100 个字符',
            ERROR_ALREADY_APPLY => '您已申请过主播了',
            ERROR_MUST_BIND_PHONE => '必须先绑定手机',
            ERROR_WECHAT_NUM_FORMAT => '微信号应该只包含字母和数字, 请到微信设置中查看',

            //packets
            ERROR_PACKET_TOO_LITTLE => '红包最低1元',
            ERROR_PACKET_TOO_MUCH => '红包最多1万元',
            ERROR_PACKET_NONE => '红包已经被抢光了',
            ERROR_ALREADY_GRAB => '您已经抢过红包了',
            ERROR_PACKET_SEND => '发红包出错了',
            ERROR_PACKET_AT_LEAST => '红包每个至少1元',

            // accounts
            ERROR_VERIFY_RECEIPT => '验证支付凭证失败',

            // withdraws
            ERROR_MUST_SUBSCRIBE => '为了转账给您,必须先关注平方根科技服务号',
            ERROR_EXCEED_BALANCE => '提现金额超过了账户余额',
            ERROR_WITHDRAW_AMOUNT_TOO_LITTLE => '提现金额最低不少于5元',
            ERROR_HAVE_WAIT_WITHDRAW => '有正在处理的提现中,请等待处理完成',
            ERROR_HAVE_WAIT_LIVE => '当前还有未完成的直播,不允许提现'
        );
    }

}

// pay
define('LEAST_COMMON_PAY', 1);
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
define('KEY_NEED_PAY', 'needPay');
define('KEY_NOTICE', 'notice');
define('KEY_SHARE_ICON', 'shareIcon');

define('SHARE_ICON_AVATAR', 0);
define('SHARE_ICON_COVER', 1);

define('LIVE_STATUS_PREPARE', 1);
define('LIVE_STATUS_REVIEW', 5);
define('LIVE_STATUS_WAIT', 10);
define('LIVE_STATUS_ON', 20);
define('LIVE_STATUS_TRANSCODE', 25);
define('LIVE_STATUS_OFF', 30);
define('LIVE_STATUS_ERROR', 35);

if (!function_exists('liveStatusSet')) {
    function liveStatusSet()
    {
        return array(LIVE_STATUS_PREPARE, LIVE_STATUS_REVIEW, LIVE_STATUS_WAIT,
            LIVE_STATUS_ON, LIVE_STATUS_TRANSCODE, LIVE_STATUS_OFF, LIVE_STATUS_ERROR);
    }
}

if (!function_exists('channelSet')) {
    function channelSet()
    {
        return array(CHANNEL_WECHAT_H5, CHANNEL_WECHAT_QRCODE, CHANNEL_ALIPAY_APP,
            CHANNEL_WECHAT_APP, CHANNEL_APPLE_IAP);
    }
}

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
define('KEY_WECHAT_SUBSCRIBE', 'wechatSubscribe');

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
define('CHANNEL_WECHAT_QRCODE', 'wechat_qrcode');
define('CHANNEL_ALIPAY_APP', 'alipay_app');
define('CHANNEL_WECHAT_APP', 'wechat_app');
define('CHANNEL_APPLE_IAP', 'apple_iap');

define('KEY_PREPAY_ID', 'prepayId');

// attendances
define('TABLE_ATTENDANCES', 'attendances');
define('KEY_ATTENDANCE_ID', 'attendanceId');
define('KEY_ATTENDANCE_COUNT', 'attendanceCount');
define('KEY_NOTIFIED', 'notified');
define('KEY_WECHAT_NOTIFIED', 'wechatNotified');
define('KEY_VIDEO_NOTIFIED', 'videoNotified');

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
define('TRANS_TYPE_WITHDRAW', 4);

define('REMARK_ALIPAY', '支付宝充值');
define('REMARK_WECHAT', '微信充值');
define('REMARK_WECHAT_QRCODE', '微信二维码充值');
define('REMARK_APPLE_IAP', '苹果应用内购充值');
define('REMARK_PAY', '参加%s的直播');
define('REMARK_INCOME_LIVE', '%s报名直播');
define('REMARK_ATTEND', '%s参加%s的直播');
define('REMARK_REWARD', '%s打赏%s的直播');
define('REMARK_WITHDRAW', '提现');

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
define('KEY_UNION_ID', 'unionId');
define('KEY_PLATFORM', 'platform');
define('PLATFORM_WECHAT', 'wechat');
define('PLATFORM_WECHAT_APP', 'wechat_app');
define('PLATFORM_WXAPP', 'wxapp');

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

// coupons
define('TABLE_COUPONS', 'coupons');
define('KEY_PHONE', 'phone');
define('COUPON_ID', 'couponId');

// recorded videos
define('TABLE_RECORDED_VIDEOS', 'recorded_videos');
define('KEY_RECORDED_VIDEO_ID', 'recordedVideoId');
define('KEY_FILE_NAME', 'fileName');
define('KEY_TRANSCODED', 'transcoded');
define('KEY_TRANSCODED_TIME', 'transcodedTime');
define('KEY_TRANSCODED_FILE_NAME', 'transcodedFileName');

// videos
define('TABLE_VIDEOS', 'videos');
define('KEY_VIDEO_ID', 'videoId');
define('KEY_TITLE', 'title');
define('VIDEO_TYPE_MP4', 'mp4');
define('VIDEO_TYPE_M3U8', 'm3u8');

// rewards
define('TABLE_REWARDS', 'rewards');
define('KEY_REWARD_ID', 'rewardId');

// staffs
define('TABLE_STAFFS', 'staffs');
define('KEY_STAFF_ID', 'staffId');

define('TABLE_LIVE_VIEWS', 'live_views');
define('KEY_LIVE_VIEW_ID', 'liveViewId');
define('KEY_LIVE_STATUS', 'liveStatus');
define('KEY_ENDED', 'ended');

define('VIEW_PLATFORM_WECHAT', 'wechat');
define('VIEW_PLATFORM_PC', 'pc');
define('VIEW_PLATFORM_IOS', 'ios');
define('VIEW_PLATFORM_ANDROID', 'android');
define('VIEW_PLATFORM_WECHAT_APP', 'wechat_app');

// applications
define('TABLE_APPLICATIONS', 'applications');
define('KEY_APPLICATION_ID', 'applicationId');
define('KEY_WECHAT_ACCOUNT', 'wechatAccount');
define('KEY_SOCIAL_ACCOUNT', 'socialAccount');
define('KEY_INTRODUCTION', 'introduction');
define('KEY_REVIEW_REMARK', 'reviewRemark');
define('KEY_REVIEW_NOTIFIED', 'reviewNotified');
define('KEY_NAME', 'name');

define('APPLICATION_STATUS_REVIEWING', 1);
define('APPLICATION_STATUS_SUCCEED', 5);
define('APPLICATION_STATUS_REJECT', 10);

define('MAX_SOCIAL_ACCOUNT_LEN', 200);
define('MAX_INTRODUCTION_LEN', 500);
define('MAX_REVIEW_MARK_LEN', 100);

// packets
define('TABLE_PACKETS', 'packets');
define('KEY_PACKET_ID', 'packetId');
define('KEY_TOTAL_AMOUNT', 'totalAmount');
define('KEY_TOTAL_COUNT', 'totalCount');
define('KEY_WISHING', 'wishing');
define('KEY_REMAIN_COUNT', 'remainCount');

// user_packets
define('TABLE_USER_PACKETS', 'user_packets');
define('KEY_USER_PACKET_ID', 'userPacketId');

define('KEY_OP', 'op');

define('OP_ADD', 'add');
define('OP_DEL', 'del');

// topics
define('TABLE_TOPICS', 'topics');
define('KEY_TOPIC_ID', 'topicId');

// subscribes
define('TABLE_SUBSCRIBES', 'subscribes');
define('KEY_SUBSCRIBE_ID', 'subscribeId');

// withdraws
define('TABLE_WITHDRAWS', 'withdraws');
define('KEY_WITHDRAW_ID', 'withdrawId');
define('WITHDRAW_STATUS_WAIT', 1);
define('WITHDRAW_STATUS_REJECT', 5);
define('WITHDRAW_STATUS_FINISH', 10);

if (!function_exists('viewPlatformSet')) {
    function viewPlatformSet()
    {
        return array(VIEW_PLATFORM_WECHAT, VIEW_PLATFORM_PC,
            VIEW_PLATFORM_IOS, VIEW_PLATFORM_ANDROID, VIEW_PLATFORM_WECHAT_APP);
    }
}

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

define('WEB_WECHAT_APP_ID', 'wxe80a6d2b5d54985c');
define('WEB_WECHAT_APP_SECRET', 'd4b8c9b89e8786c54b6ea66cbfccc5a8');

define('MOBILE_WECHAT_APP_ID', 'wxcc3f309821d8cab5');
define('MOBILE_WECHAT_APP_SECRET', 'eb4b16d73322bd06e41db5eda8549400');

define('WXAPP_APPID', 'wxf7383d242dd0d2b7');
define('WXAPP_SECRET', 'd855fa3f5d519fbd4be1e30ac08881cd');


define('QINIU_FILE_HOST', 'http://i.quzhiboapp.com');
define('QINIU_ACCESS_KEY', '-ON85H3cEMUaCuj8UFpLELeEunEAqslrqYqLbn9g');
define('QINIU_SECRET_KEY', 'X-oHOYDinDEhNk5nr74O1rKDvkmPq0ZQwEZfFt6x');
define('QINIU_HUB', 'qulive');

define('WECHAT_TOKEN', 'mLYmAdMrVkKO');

define('KEY_URL', 'url');

// live hooks
define('KEY_STREAM', 'stream');
define('KEY_ACTION', 'action');
define('KEY_FILE', 'file');

define('KEY_TEXT', 'text');

define('NOTIFY_TYPE_SMS', 'sms');
define('NOTIFY_TYPE_WECHAT', 'wechat');

define('ORIGIN_VIDEO_DIR', '/root/srs/trunk/objs/nginx/html/live/');

if (ENVIRONMENT == 'development') {
    define('NGINX_VIDEO_DIR', '/home/www/test_videos/');
} else {
    define('NGINX_VIDEO_DIR', '/home/www/videos/');
}

if (ENVIRONMENT == 'development') {
    define('VIDEO_WORKING_DIR', '/Users/lzw/square-root/videos/');
} else {
    define('VIDEO_WORKING_DIR', '/home/videos/');
}

if (ENVIRONMENT == 'development') {
    define('REPLAY_WORKING_DIR', '/Users/lzw/square-root/replay_videos/');
} else {
    define('REPLAY_WORKING_DIR', '/home/replay_videos/');
}

define('CHEER_HOST_PASSWORD', 'Quzhiboapp2046');
define('VIDEO_HOST_PASSWORD', 'Quzhiboapp1314');

define('FFMPEG_PATH', '/home/srs/trunk/objs/ffmpeg/bin/ffmpeg');

// reward
define('LEAST_COMMON_REWARD', 100);
define('MAX_COMMON_REWARD', 100 * 1000);

// packets
define('LEAST_COMMON_PACKET', 100);
define('MAX_COMMON_PACKET', 100 * 100000);

// charge type
define('CHARGE_TYPE_ATTEND', 1);
define('CHARGE_TYPE_REWARD', 2);
define('CHARGE_TYPE_PACKET', 3);
define('CHARGE_TYPE_BALANCE', 4);

// oauth type
define('OAUTH_RESULT_LOGIN', 'login');
define('OAUTH_RESULT_REGISTER', 'register');
define('OAUTH_USER', 'user');
define('OAUTH_SNS_USER', 'snsUser');

define('LC_MAX_NAME_LEN', 5);

define('LIVE_INIT_MAX_PEOPLE', 2000);

define('KEY_SKIP_LIVE_ID', 'skipLiveId');

define('TRANSCODE_QUEUE', 100);

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

define('VIDEO_HOST_URL', 'http://video-qncdn.quzhiboapp.com/');
define('VIDEO_ALI_HOST_URL', 'http://video-cdn.quzhiboapp.com/');


define('TMP_WECHAT_ACCESS_TOKEN',
'qwkXYOvNLxY0zbKaTTIJ38171HZrD0vvC3x6BDcMCKjAjsC9Ki8B6xFPYq7eHzVSJ-g0LAoqgtbndYplIFiOcAmcDquK5KxvxTpCcDmYLufz04zeZ6BDs-bktS6r4c72DMDbAIAUDU');
define('TMP_WECHAT_JSAPI_TICKET',
'kgt8ON7yVITDhtdwci0qec2RIVDMtWfd2c6nCNNWHicobPlBpFb150TPqeHA5ga_SAhnOCe8SfNPnUts-qhK_Q');

define('TMP_WXAPP_ACCESS_TOKEN',
'0K6zPlK1CkkEYCJPhyxCS0iPmQe4M2N2DX5ZG59fDJmE6_n3LLNhiQGq3m_LRInMy6ydENRIDKGVFs2B9YUx5HlbXshjlKIjSHjM3bBhHZ_bcx8bo7mL6dUIbulHO79KWPGiADAGZQ');

define('WECHAT_API_BASE', 'https://api.weixin.qq.com/');
define('WECHAT_API_CGIBIN', WECHAT_API_BASE . 'cgi-bin/');

define('KEY_TO_USER_NAME', 'ToUserName');
define('KEY_FROM_USER_NAME', 'FromUserName');
define('KEY_CREATE_TIME', 'CreateTime');
define('KEY_MSG_TYPE', 'MsgType');
define('KEY_CONTENT', 'Content');
define('KEY_FUNC_FLAG', 'FuncFlag');
define('KEY_EVENT', 'Event');
define('KEY_EVENT_KEY', 'EventKey');

define('MSG_TYPE_TEXT', 'text');
define('MSG_TYPE_EVENT', 'event');
define('EVENT_SUBSCRIBE', 'subscribe');
define('EVENT_UNSUBSCRIBE', 'unsubscribe');
define('EVENT_VIEW', 'view');
define('EVENT_SCAN', 'SCAN');

define('WECHAT_WELCOME_WORD', <<<EOD
欢迎关注趣直播，趣直播是知识直播平台，邀请了大咖来分享知识或经历。

<a href="http://m.quzhiboapp.com/?liveId=0">最新直播</a>

%s
EOD
);

define('WECHAT_LIVE_WORD', <<<EOD
欢迎参与直播，请点击进入报名：<a href="http://m.quzhiboapp.com/?liveId=%s&type=live">%s</a>
EOD
);

define('WECHAT_PACKET_WORD', <<<EOD
您有一个%s的红包 <a href="http://m.quzhiboapp.com/?packetId=%s&type=packet">点击我进入领取</a>
EOD
);

define('ROW_MAX', 10000 * 10000);

define('KEY_THIRD_SESSION', 'thirdSession');
define('KEY_RAW_DATA', 'rawData');
define('KEY_ENCRYPTED_DATA', 'encryptedData');
define('KEY_SIGNATURE', 'signature');
define('KEY_IV', 'iv');
define('KEY_USER_INFO', 'userInfo');

define('THIRD_SESSION_LEN', 48);

define('KEY_USER_IDS', 'userIds');

define('KEY_RECEIPT', 'receipt');

define('MIN_WITHDRAW_AMOUNT', 5 * 100);