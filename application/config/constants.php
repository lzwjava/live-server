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

define('ERROR_USERNAME_TAKEN', 'username_token');
define('ERROR_MOBILE_PHONE_NUMBER_TAKEN', 'phone_number_taken');
define('ERROR_SMS_WRONG', 'sms_wrong');
define('ERROR_MISS_PARAMETERS', 'missing_parameters');
define('ERROR_AT_LEAST_ONE_UPDATE', 'at_least_one_update');
define('ERROR_NOT_IN_SESSION', 'not_in_session');
define('ERROR_USER_NOT_EXIST', 'user_not_exists');
define('ERROR_OBJECT_NOT_EXIST', 'object_not_exists');
define('ERROR_LOGIN_FAILED', 'login_failed');
define('ERROR_UNKNOWN_TYPE', 'unknown_type');
define('ERROR_NOT_ALLOW_DO_IT', 'not_allow_do_it');
define('ERROR_PARAMETER_ILLEGAL', 'parameter_illegal');
define('ERROR_INVALID_IP', 'invalid_ip');
define('ERROR_ALREADY_DO_IT', 'already_do_it');
define('ERROR_PASSWORD_FORMAT', 'password_format_wrong');
define('ERROR_RUN_SQL_FAILED', 'run_sql_failed');
define('ERROR_URL_NOT_WORKING', 'url_not_working');

define('TYPE_REVIEWER', 'reviewer');
define('TYPE_LEARNER', 'learner');

define('KEY_COOKIE_TOKEN', 'SessionToken');
define('COOKIE_VID', 'vid');
define('KEY_SESSION_HEADER', 'X-Session');

define('KEY_SKIP', 'skip');
define('KEY_LIMIT', 'limit');

define('KEY_SORT', 'sort');

// users table
define('KEY_MOBILE_PHONE_NUMBER', 'mobilePhoneNumber');
define('KEY_AVATAR_URL', 'avatarUrl');
define('KEY_SESSION_TOKEN', 'sessionToken');
define('KEY_SESSION_TOKEN_CREATED', 'sessionTokenCreated');
define('KEY_PASSWORD', 'password');
define('KEY_USERNAME', 'username');
define('KEY_TYPE', 'type');
define('KEY_VALID', 'valid');

define('KEY_SMS_CODE', 'smsCode');

define('KEY_TAGS', 'tags');

define('KEY_CREATED', 'created');
define('KEY_UPDATED', 'updated');

define('TABLE_USERS', 'users');

define('KEY_USER_ID', 'userId');

// sms
define('SMS_TEMPLATE', 'template');
define('SMS_REVIEWER', 'reviewer');
define('SMS_LEARNER', 'learner');
define('SMS_CODE_URL', 'codeUrl');
define('SMS_REVIEW_URL', 'reviewUrl');

// images
define('TABLE_IMAGES', 'images');
define('KEY_IMAGE_ID', 'imageId');
define('KEY_DESCRIPTION', 'description');
define('KEY_LINK', 'link');
define('KEY_TOPIC', 'topic');
define('KEY_AUTHOR', 'author');
define('KEY_TITLE', 'title');
define('KEY_WIDTH', 'width');
define('KEY_HEIGHT', 'height');

define('KEY_IMAGE_IDS', 'imageIds');

// posts, post_images
define('KEY_POST_ID', 'postId');
define('TABLE_POSTS', 'posts');
define('KEY_SCORE', 'score');
define('KEY_COVER', 'cover');

define('KEY_UP', 'up');
define('KEY_DOWN', 'down');

define('TABLE_POST_IMAGES', 'post_images');

// post_votes
define('TABLE_POST_VOTES', 'post_votes');
define('KEY_VOTE', 'vote');

// comments
define('TABLE_COMMENTS', 'comments');
define('KEY_COMMENT_ID', 'commentId');
define('KEY_PARENT_ID', 'parentId');
define('KEY_CONTENT', 'content');
define('KEY_AUTHOR_ID', 'authorId');

// comment_votes
define('TABLE_COMMENT_VOTES', 'comment_votes');

define('KEY_POINTS', 'points');


define('QN_ACCESS_KEY', '1qr0lyWewsDOAnSXqYOUPGWKJEGPGDhOgRgB_K7K');
define('QN_SECRET_KEY', '1sEhu-pQJh8wbtgQnHe6caT81o1ATTGoI5u-i4-H');
