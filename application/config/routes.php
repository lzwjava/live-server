<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// users
$route['self']['POST'] = 'users/update';
$route['self']['GET'] = 'users/self';
$route['users']['POST'] = 'users/register';
$route['login']['POST'] = 'users/login';
$route['logout']['GET'] = 'users/logout';
$route['requestSmsCode']['POST'] = 'users/requestSmsCode';
$route['users/isRegister']['GET'] = 'users/isRegister';
$route['users/registerBySns']['POST'] = 'users/registerBySns';
$route['users/(\d+)']['GET'] = 'users/one/$1';
$route['users/fixAvatarUrl']['GET'] = 'users/fixAvatarUrl';
$route['users/bindPhone']['POST'] = 'users/bindPhone';
$route['users/list']['POST'] = 'users/list';
$route['users/userTopic']['GET'] = 'users/userTopic';
$route['users/fixSystemId']['GET'] = 'users/fixSystemId';

// lives
$route['lives/on']['GET'] = 'lives/list';
$route['lives/recommend']['GET'] = 'lives/recommend';
$route['lives/(\d+)']['GET'] = 'lives/one/$1';
$route['lives']['POST'] = 'lives/create';
$route['lives/(\d+)']['POST'] = 'lives/update/$1';
$route['lives/(\d+)/alive']['GET'] = 'lives/alive/$1';
$route['lives/(\d+)/end']['GET'] = 'lives/end/$1';
$route['lives/(\d+)/begin']['GET'] = 'lives/begin/$1';
$route['lives/(\d+)/submitReview']['GET'] = 'lives/submitReview/$1';
$route['lives/(\d+)/publish']['GET'] = 'lives/publish/$1';
$route['lives/lastPrepare']['GET'] = 'lives/lastPrepare';
$route['lives/attended']['GET'] = 'lives/attended';
$route['lives/me']['GET'] = 'lives/my';
$route['lives/(\d+)/users']['GET'] = 'lives/attendedUsers/$1';
$route['lives/(\d+)/notify'] = 'lives/notifyLiveStart/$1';
$route['lives/fixAttendanceCount'] = 'lives/fixAttendanceCount';
$route['lives/(\d+)/groupSend'] = 'lives/groupSend/$1';
$route['lives/(\d+)/wait'] = 'lives/setWait/$1';
$route['lives/(\d+)/setReview'] = 'lives/setReview/$1';
$route['lives/(\d+)/notifyVideo']['GET'] = 'lives/notifyVideo/$1';
$route['lives/(\d+)/import']['GET'] = 'lives/import/$1';
$route['lives/(\d+)/finish']['GET'] = 'lives/finish/$1';
$route['lives/(\d+)/error']['GET'] = 'lives/error/$1';
$route['lives/(\d+)/notifyRelated']['GET'] = 'lives/notifyLiveStartRecommend/$1';
$route['lives/(\d+)/topic']['POST'] = 'lives/updateTopic/$1';
$route['lives/(\d+)/notifyNewLive']['GET'] = 'lives/notifyNewLive/$1';

// recorded videos
$route['lives/(\d+)/recordedVideos'] = 'recordedVideos/list/$1';
$route['recordedVideos/convert']['GET'] = 'recordedVideos/convert';
$route['recordedVideos/replay']['GET'] = 'recordedVideos/replay';

$route['jobs/alive']['GET'] = 'jobs/alive';

// attendances
$route['attendances']['POST'] = 'attendances/create';
$route['attendances/one']['GET'] = 'attendances/one';
$route['attendances/me']['GET'] = 'attendances/myList';
$route['attendances/lives/(\d+)']['GET'] = 'attendances/liveList/$1';
$route['attendances/refund/(\d+)']['GET'] = 'attendances/refund/$1';
$route['attendances/transfer']['GET'] = 'attendances/transfer';
$route['attendances/invites']['GET'] = 'attendances/invites';

// qrcodes
$route['qrcodes']['POST'] = 'qrcodes/scanQrcode';
$route['qrcodes/scanned']['GET'] = 'qrcodes/isQrcodeScanned';
$route['qrcodes/gen']['GET'] = 'qrcodes/png';
$route['qrcodes/one']['GET'] = 'qrcodes/qrcode';

// rewards
$route['rewards/notify']['POST'] = 'rewards/notify';
$route['rewards']['POST'] = 'rewards/create';
$route['lives/(\d+)/rewards'] = 'rewards/list/$1';

// wechat
$route['wechat/sign']['GET'] = 'wechat/sign';
$route['wechat/oauth']['GET'] = 'wechat/oauth';
$route['wechat/silentOauth']['GET'] = 'wechat/silentOauth';
$route['wechat/wxpayNotify']['GET'] = 'wechat/wxpayNotify';
$route['wechat/webOauth']['GET'] = 'wechat/webOauth';
$route['wechat/bind']['GET'] = 'wechat/bind';
$route['wechat/callback']['GET'] = 'wechat/callback';
$route['wechat/callback']['POST'] = 'wechat/callback';
$route['wechat/appOauth']['GET'] = 'wechat/appOauth';
$route['wechat/isSubscribe']['GET'] = 'wechat/isSubscribe';
$route['wechat/createMenu']['GET'] = 'wechat/createMenu';
$route['wechat/menu']['GET'] = 'wechat/menu';
$route['wechat/fixAllSubscribe']['GET'] = 'wechat/fixAllSubscribe';
$route['wechat/qrcode']['GET'] = 'wechat/qrcode';
$route['wechat/addNews']['GET'] = 'wechat/addNews';
$route['wechat/uploadImg']['GET'] = 'wechat/uploadImg';
$route['wechat/sendMassMsg']['GET'] = 'wechat/sendMassMsg';

// wxapp
$route['wechat/login']['POST'] = 'wechat/login';
$route['wechat/registerByApp']['POST'] = 'wechat/registerByApp';

// live hooks
$route['liveHooks/onPublish']['POST'] = 'liveHooks/onPublish';
$route['liveHooks/onUnPublish']['POST'] = 'liveHooks/onUnPublish';
$route['liveHooks/onDvr']['POST'] = 'liveHooks/onDvr';

// shares
$route['shares']['POST'] = 'shares/create';

// coupons
$route['coupons']['POST'] = 'coupons/create';

// videos
$route['videos/import']['GET'] = 'videos/import';
$route['videos/mp4Ready']['GET'] = 'videos/mp4Ready';
$route['lives/(\d+)/videos']['GET'] = 'videos/list/$1';
$route['lives/(\d+)/videos']['POST'] = 'videos/create/$1';


// live views
$route['liveViews']['POST'] = 'liveViews/create';
$route['liveViews/(\d+)/end']['GET'] = 'liveViews/end/$1';

// staffs
$route['staffs']['POST'] = 'staffs/create';
$route['staffs']['GET'] = 'staffs/list';

// charges
$route['charges/one']['GET'] = 'charges/one';
$route['charges/remark']['POST'] = 'charges/remark';
$route['charges']['POST'] = 'charges/create';
$route['charges/appleCallback']['POST'] = 'charges/appleCallback';

// stats
$route['stats']['GET'] = 'stats/all';

// applications
$route['applications']['POST'] = 'applications/create';
$route['applications/(\d+)']['POST'] = 'applications/update/$1';
$route['applications/(\d+)/succeed']['POST'] = 'applications/reviewSucceed/$1';
$route['applications/(\d+)/reject']['POST'] = 'applications/reviewReject/$1';
$route['applications/(\d+)']['GET'] = 'applications/one/$1';
$route['applications/me']['GET'] = 'applications/me/$1';

// packets
$route['packets']['POST'] = 'packets/create';
$route['packets/(\w+)/grab']['GET'] = 'packets/grab/$1';
$route['packets/me']['GET'] = 'packets/myPacket';
$route['packets/meAll']['GET'] = 'packets/meAll';
$route['packets/sendPacket']['GET'] = 'packets/sendPacket';
$route['packets/(\w+)']['GET'] = 'packets/one/$1';
$route['packets/(\w+)/userPackets']['GET'] = 'packets/allPacketsById/$1';

// topics
$route['topics']['POST'] = 'topics/create';
$route['topics']['GET'] = 'topics/list';
$route['topics/init']['GET'] = 'topics/init';

// subscribes
$route['subscribes']['POST'] = 'subscribes/create';
$route['subscribes/del']['POST'] = 'subscribes/del';

// accounts
$route['accounts/me']['GET'] = 'accounts/me';
$route['accounts/initIncome']['GET'] = 'accounts/initIncome';

// withdraws
$route['withdraws']['POST'] = 'withdraws/create';
$route['withdraws']['GET'] = 'withdraws/list';
$route['withdraws/(\d+)/agree']['GET'] = 'withdraws/agree/$1';
$route['withdraws/manual']['POST'] = 'withdraws/createByManual';
$route['withdraws/withdrawAll']['GET'] = 'withdraws/withdrawAll';
