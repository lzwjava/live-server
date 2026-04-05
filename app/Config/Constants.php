<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2_592_000);
defined('YEAR')   || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
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
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);        // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);          // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);         // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);   // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);  // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);     // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);       // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);      // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);    // highest automatically-assigned error code
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);
defined('FILE_READ_MODE') OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE') OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE') OR define('DIR_WRITE_MODE', 0755);
defined('FOPEN_READ') OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESCTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');
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
define('ERROR_NOT_ATTEND_LIVE', 'not_attend_live');
define('ERROR_NOT_LIVE_TOPIC', 'not_live_topic');
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
define('ERROR_LIVE_NOT_REVIEW', 'live_not_review');
define('ERROR_LIVE_NOT_ON', 'live_not_on');
define('ERROR_SPEAKER_INTRO_TOO_SHORT', 'speaker_intro_too_short');
define('ERROR_PLAYBACK_FAIL', 'playback_fail');
define('ERROR_LIVE_BEGIN_EARLY', 'live_begin_early');
define('ERROR_ALREADY_ATTEND', 'already_attend');
define('ERROR_CHARGE_CREATE', 'create_charge_failed');
define('ERROR_NOT_ALLOW_ATTEND', 'not_allow_attend');
define('ERROR_BALANCE_INSUFFICIENT', 'balance_insufficient');
define('ERROR_TRANS_FAILED', 'trans_failed');
define('ERROR_QRCODE_INVALID', 'qrcode_invalid');
define('ERROR_QRCODE_DUPLICATE', 'qrcode_duplicate');
define('ERROR_PARTNER_OR_SERVICE', 'partner_or_service_wrong');
define('ERROR_SIGN_FAILED', 'sign_failed');
define('ERROR_ALREADY_NOTIFY', 'already_notify');
define('ERROR_LC_CONVERSATION_FAILED', 'lc_conversation_failed');
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
define('ERROR_SESSION_KEY_NOT_EXISTS', 'session_key_not_exists');
define('ERROR_WX_ENCRYPT', 'wx_encrypt');
define('ERROR_WX_SIGN', 'wx_sign');
define('ERROR_WX_SESSION_EXPIRE', 'wx_session_expire');
define('ERROR_WX_SESSION_LEN', 'wx_session_len');
define('ERROR_WX_SNS_USER_NOT_EXISTS', 'sns_user_not_exists');
define('ERROR_VIDEOS_NOT_GEN', 'video_not_gen');
define('ERROR_FAIL_HANDLE_VIDEO', 'fail_handle_video');
define('ERROR_CONVERT_VIDEO', 'fail_convert_video');
define('ERROR_MERGE_VIDEO', 'fail_merge_video');
define('ERROR_SCP_FAIL', 'fail_scp');
define('ERROR_REWARD_TOO_LITTLE', 'reward_too_little');
define('ERROR_REWARD_TOO_MUCH', 'reward_too_much');
define('ERROR_REWARD_YOURSELF', 'reward_yourself');
define('ERROR_NOT_ATTEND', 'not_attend');
define('ERROR_ALREADY_STAFF', 'already_staff');
define('ERROR_SOCIAL_ACCOUNT_ELN', 'social_account_len');
define('ERROR_INTRODUCTION_LEN', 'introduction_len');
define('ERROR_REVIEW_REMARK_LEN', 'review_remark_len');
define('ERROR_ALREADY_APPLY', 'already_apply');
define('ERROR_WECHAT_NUM_FORMAT', 'wechat_num_format');
define('ERROR_MIN_INTRODUCTION_LEN', 'min_introduction_len');
define('ERROR_PACKET_TOO_LITTLE', 'packet_too_little');
define('ERROR_PACKET_TOO_MUCH', 'packet_too_much');
define('ERROR_PACKET_NONE', 'packet_none');
define('ERROR_ALREADY_GRAB', 'packet_already_grab');
define('ERROR_PACKET_SEND', 'packet_send');
define('ERROR_PACKET_AT_LEAST', 'packet_at_least');
define('ERROR_VERIFY_RECEIPT', 'verify_receipt_wrong');
define('ERROR_MUST_SUBSCRIBE', 'must_subscribe');
define('ERROR_EXCEED_BALANCE', 'exceed_balance');
define('ERROR_WITHDRAW_AMOUNT_TOO_LITTLE', 'withdraw_amount_too_little');
define('ERROR_HAVE_WAIT_WITHDRAW', 'have_wait_withdraw');
define('ERROR_HAVE_WAIT_LIVE', 'have_wait_live');
define('ERROR_WITHDRAW_ALREADY_DONE', 'withdraw_already_done');
define('ERROR_NO_AVAILABLE_GROUP', 'no_available_group');
define('LEAST_COMMON_PAY', 1);
define('MAX_COMMON_PAY', 100 * 1000);
define('KEY_SKIP', 'skip');
define('KEY_LIMIT', 'limit');
define('KEY_CREATED', 'created');
define('KEY_UPDATED', 'updated');
define('TABLE_LIVES', 'lives');
define('KEY_LIVE_ID', 'liveId');
define('KEY_SUBJECT', 'subject');
define('KEY_RTMP_KEY', 'rtmpKey');
define('KEY_STATUS', 'status');
define('KEY_CONVERSATION_ID', 'conversationId');
define('KEY_COVER_URL', 'coverUrl');
define('KEY_COURSEWARE_KEY', 'coursewareKey');    // 课件上传qiniu的key
define('KEY_LIVE_QRCODE_KEY', 'liveQrcodeKey');    // 允许讲师上传二维码导流
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
define('KEY_LIVE_KEYWORD', 'keyword');
define('SHARE_ICON_AVATAR', 0);
define('SHARE_ICON_COVER', 1);
define('LIVE_STATUS_PREPARE', 1);
define('LIVE_STATUS_REVIEW', 5);
define('LIVE_STATUS_WAIT', 10);
define('LIVE_STATUS_ON', 20);
define('LIVE_STATUS_TRANSCODE', 25);
define('LIVE_STATUS_OFF', 30);
define('LIVE_STATUS_ERROR', 35);
define('SMS_TEMPLATE', 'template');
define('SMS_NAME', 'name');
define('SMS_OPEN_APP_WORDS', 'openAppWords');
define('SMS_INTRO', 'intro');
define('SMS_TIME', 'time');
define('SMS_OWNER_NAME', 'ownerName');
define('SMS_LINK', 'link');
define('TABLE_USERS', 'users');
define('KEY_MOBILE_PHONE_NUMBER', 'mobilePhoneNumber');
define('KEY_AVATAR_URL', 'avatarUrl');
define('KEY_SESSION_TOKEN', 'sessionToken');
define('KEY_SESSION_TOKEN_CREATED', 'sessionTokenCreated');
define('KEY_PASSWORD', 'password');
define('KEY_USERNAME', 'username');
define('KEY_USER_ID', 'userId');
define('KEY_WECHAT_SUBSCRIBE', 'wechatSubscribe');
define('KEY_LIVE_SUBSCRIBE', 'liveSubscribe');
define('KEY_INCOME_SUBSCRIBE', 'incomeSubscribe');    // 是否接收微信收入通知
define('KEY_SMS_CODE', 'smsCode');
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
define('TABLE_ATTENDANCES', 'attendances');
define('KEY_ATTENDANCE_ID', 'attendanceId');
define('KEY_ATTENDANCE_COUNT', 'attendanceCount');
define('KEY_FROM_USER_ID', 'fromUserId');
define('KEY_NOTIFIED', 'notified');
define('KEY_FIRST_NOTIFIED', 'firstNotified');
define('KEY_PRE_NOTIFIED', 'preNotified');
define('KEY_WECHAT_NOTIFIED', 'wechatNotified');
define('KEY_VIDEO_NOTIFIED', 'videoNotified');
define('TABLE_TRANSACTIONS', 'transactions');
define('KEY_TRANSACTION_ID', 'transactionId');
define('KEY_OLD_BALANCE', 'oldBalance');
define('KEY_RELATED_ID', 'relatedId');
define('KEY_TYPE', 'type');
define('KEY_REMARK', 'remark');
define('TABLE_ACCOUNTS', 'accounts');
define('KEY_ACCOUNT_ID', 'accountId');
define('KEY_BALANCE', 'balance');
define('KEY_INCOME', 'income');
define('TRANS_TYPE_RECHARGE', 1);
define('TRANS_TYPE_PAY', 2);
define('TRANS_TYPE_INCOME', 3);
define('TRANS_TYPE_WITHDRAW', 4);
define('TRANS_TYPE_LIVE_INCOME', 30);
define('TRANS_TYPE_REWARD_INCOME', 31);
define('TRANS_TYPE_INVITE_INCOME', 32);
define('REMARK_ALIPAY', '支付宝充值');
define('REMARK_WECHAT', '微信充值');
define('REMARK_WECHAT_QRCODE', '微信二维码充值');
define('REMARK_APPLE_IAP', '苹果应用内购充值');
define('REMARK_PAY', '参加%s的直播');
define('REMARK_INCOME_LIVE', '%s报名直播');
define('REMARK_ATTEND', '%s参加%s的直播');
define('REMARK_REWARD', '%s打赏%s的直播');
define('REMARK_WITHDRAW', '提现');
define('REMARK_SYSTEM_INCOME', '平台分成');
define('REMARK_INVITE_INCOME', '邀请分成');
define('TABLE_SCANNED_QRCODES', 'scanned_qrcodes');
define('KEY_QRCODE_ID', 'qrcodeId');
define('KEY_CODE', 'code');
define('KEY_SCANNED', 'scanned');
define('KEY_DATA', 'data');
define('TYPE_CREATE', 0);
define('TYPE_WATCH', 1);
define('TABLE_SNS_USERS', 'sns_users');
define('KEY_SNS_USER_ID', 'snsUserId');
define('KEY_OPEN_ID', 'openId');
define('KEY_UNION_ID', 'unionId');
define('KEY_PLATFORM', 'platform');
define('PLATFORM_WECHAT', 'wechat');
define('PLATFORM_WECHAT_APP', 'wechat_app');
define('PLATFORM_WXAPP', 'wxapp');
define('TABLE_STATES', 'states');
define('KEY_HASH', 'hash');
define('KEY_STATE', 'state');
define('TABLE_SHARES', 'shares');
define('KEY_SHARE_TS', 'shareTs');
define('SHARE_CHANNEL_WECHAT_TIMELINE', 'wechat_timeline');
define('KEY_COOKIE_TOKEN', 'SessionToken');
define('COOKIE_VID', 'vid');
define('KEY_SESSION_HEADER', 'X-Session');
define('LC_TEST_APP_ID', 'YY3S7uNlnXUgX48BHTJlJx4i-gzGzoHsz');
define('LC_TEST_APP_KEY', 'h9zCqcPSi7nDgQTQE6YsOT0z');
define('LC_PROD_APP_ID', 's83aTX5nigX1KYu9fjaBTxIa-gzGzoHsz');
define('LC_PROD_APP_KEY', 'V4FPFLSmSeO1HaIwPVyhO9P3');
define('TABLE_COUPONS', 'coupons');
define('KEY_PHONE', 'phone');
define('COUPON_ID', 'couponId');
define('TABLE_RECORDED_VIDEOS', 'recorded_videos');
define('KEY_RECORDED_VIDEO_ID', 'recordedVideoId');
define('KEY_FILE_NAME', 'fileName');
define('KEY_TRANSCODED', 'transcoded');
define('KEY_TRANSCODED_TIME', 'transcodedTime');
define('KEY_TRANSCODED_FILE_NAME', 'transcodedFileName');
define('TABLE_VIDEOS', 'videos');
define('KEY_VIDEO_ID', 'videoId');
define('KEY_TITLE', 'title');
define('VIDEO_TYPE_MP4', 'mp4');
define('VIDEO_TYPE_M3U8', 'm3u8');
define('TABLE_REWARDS', 'rewards');
define('KEY_REWARD_ID', 'rewardId');
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
define('MIN_INTRODUCTION_LEN', 30);
define('MAX_REVIEW_MARK_LEN', 100);
define('TABLE_PACKETS', 'packets');
define('KEY_PACKET_ID', 'packetId');
define('KEY_TOTAL_AMOUNT', 'totalAmount');
define('KEY_TOTAL_COUNT', 'totalCount');
define('KEY_WISHING', 'wishing');
define('KEY_REMAIN_COUNT', 'remainCount');
define('TABLE_USER_PACKETS', 'user_packets');
define('KEY_USER_PACKET_ID', 'userPacketId');
define('KEY_OP', 'op');
define('OP_ADD', 'add');
define('OP_DEL', 'del');
define('TABLE_TOPICS', 'topics');
define('KEY_TOPIC_ID', 'topicId');
define('TABLE_SUBSCRIBES', 'subscribes');
define('KEY_SUBSCRIBE_ID', 'subscribeId');
define('TABLE_WITHDRAWS', 'withdraws');
define('KEY_WITHDRAW_ID', 'withdrawId');
define('WITHDRAW_STATUS_WAIT', 1);
define('WITHDRAW_STATUS_REJECT', 5);
define('WITHDRAW_STATUS_FINISH', 10);
define('KEY_TRANSFER', 'transfer');
define('TABLE_JOBS', 'jobs');
define('KEY_JOB_ID', 'jobId');
define('KEY_PARAMS', 'params');
define('JOB_STATUS_WAIT', 1);
define('JOB_STATUS_CANCEL', 3);
define('JOB_STATUS_DOING', 5);
define('JOB_STATUS_DONE', 10);
define('JOB_STATUS_FAIL', 15);
define('KEY_REPORT', 'report');
define('KEY_TRIGGER_TS', 'triggerTs');
define('JOB_NAME_NOTIFY_LIVE_START', 'notifyLiveStart');
define('KEY_TASK_RUNNING', 'taskRunning');
define('TABLE_WECHAT_EVENTS', 'wechat_events');
define('KEY_WECHAT_EVENT_ID', 'wechatEventId');
define('KEY_EVENT_TYPE', 'eventType');
define('TABLE_WECHAT_GROUPS', 'wechat_groups');
define('KEY_GROUP_ID', 'groupId');
define('KEY_GROUP_USER_NAME', 'groupUserName');
define('KEY_QRCODE_KEY', 'qrcodeKey');
define('KEY_MEMBER_COUNT', 'memberCount');
define('KEY_USED', 'used');
define('KEY_QRCODE_URL', 'qrcodeUrl');
    define('LC_APP_ID', LC_TEST_APP_ID);
    define('LC_APP_KEY', LC_TEST_APP_KEY);
    define('LC_APP_ID', LC_PROD_APP_ID);
    define('LC_APP_KEY', LC_PROD_APP_KEY);
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
define('QINIU_FILE_HOST', 'https://i.quzhiboapp.com');
define('QINIU_FILE_HOST_SLASH', 'https://i.quzhiboapp.com/');
define('QINIU_FILE_HOST_WITHOUT_SCHEME', '//i.quzhiboapp.com');
define('QINIU_ACCESS_KEY', '-ON85H3cEMUaCuj8UFpLELeEunEAqslrqYqLbn9g');
define('QINIU_SECRET_KEY', 'X-oHOYDinDEhNk5nr74O1rKDvkmPq0ZQwEZfFt6x');
define('QINIU_HUB', 'qulive');
define('WECHAT_TOKEN', 'mLYmAdMrVkKO');
define('KEY_URL', 'url');
define('KEY_KEY', 'key');
define('KEY_STREAM', 'stream');
define('KEY_ACTION', 'action');
define('KEY_FILE', 'file');
define('KEY_TEXT', 'text');
define('NOTIFY_TYPE_SMS', 'sms');
define('NOTIFY_TYPE_WECHAT', 'wechat');
define('ORIGIN_VIDEO_DIR', '/root/srs/trunk/objs/nginx/html/live/');
    define('NGINX_VIDEO_DIR', '/home/www/test_videos/');
    define('NGINX_VIDEO_DIR', '/home/www/videos/');
    define('VIDEO_WORKING_DIR', '/Users/lzw/square-root/videos/');
    define('VIDEO_WORKING_DIR', '/home/videos/');
    define('REPLAY_WORKING_DIR', '/Users/lzw/square-root/replay_videos/');
    define('REPLAY_WORKING_DIR', '/home/replay_videos/');
define('CHEER_HOST_PASSWORD', 'Quzhiboapp2046');
define('VIDEO_HOST_PASSWORD', 'Quzhiboapp1314');
define('FFMPEG_PATH', '/home/srs/trunk/objs/ffmpeg/bin/ffmpeg');
define('LEAST_COMMON_REWARD', 100);
define('MAX_COMMON_REWARD', 100 * 1000);
define('LEAST_COMMON_PACKET', 100);
define('MAX_COMMON_PACKET', 100 * 100000);
define('CHARGE_TYPE_ATTEND', 1);
define('CHARGE_TYPE_REWARD', 2);
define('CHARGE_TYPE_PACKET', 3);
define('CHARGE_TYPE_BALANCE', 4);
define('OAUTH_RESULT_LOGIN', 'login');
define('OAUTH_RESULT_REGISTER', 'register');
define('OAUTH_USER', 'user');
define('OAUTH_SNS_USER', 'snsUser');
define('LC_MAX_NAME_LEN', 5);
define('LIVE_INIT_MAX_PEOPLE', 2000);
define('KEY_SKIP_LIVE_ID', 'skipLiveId');
define('TRANSCODE_QUEUE', 100);
define('VIDEO_HOST_URL', 'http://video-qncdn.quzhiboapp.com/');
define('VIDEO_ALI_HOST_URL', 'https://video-cdn.quzhiboapp.com/');
define('TMP_WECHAT_ACCESS_TOKEN',
define('TMP_WECHAT_JSAPI_TICKET',
define('TMP_WXAPP_ACCESS_TOKEN',
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
define('EVENT_VIEW', 'VIEW');
define('EVENT_SCAN', 'SCAN');
define('WECHAT_WELCOME_WORD', <<<EOD
define('WECHAT_LIVE_WORD', <<<EOD
define('WECHAT_PACKET_WORD', <<<EOD
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
define('MIN_WITHDRAW_AMOUNT', 1 * 100);
define('ADMIN_OP_USER_ID', 1);
define('ADMIN_OP_SYSTEM_ID', 100000);
define('ANCHOR_INCOME_RATE', 0);
define('INVITE_INCOME_RATE', 0.1);
define('ANCHOR_INCOME_REWARD_RATE', 0.9);
define('KEY_MEDIA_ID', 'mediaId');
define('QINIU_QULIVE_QRCODE_KEY', 'WRuqRV');    // 直播倒计时页面显示的默认二维码
