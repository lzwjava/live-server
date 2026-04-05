<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// ---------------------------------------------------------------------------
// Home
// ---------------------------------------------------------------------------
$routes->get('/', 'Home::index');

// ---------------------------------------------------------------------------
// Users
// ---------------------------------------------------------------------------
$routes->get('home', 'Home::index');
$routes->get('self', 'Users::self');
$routes->post('self', 'Users::update');
$routes->post('users', 'Users::register');
$routes->post('login', 'Users::login');
$routes->get('logout', 'Users::logout');
$routes->post('requestSmsCode', 'Users::requestSmsCode');
$routes->get('users/isRegister', 'Users::isRegister');
$routes->post('users/registerBySns', 'Users::registerBySns');
$routes->get('users/fixAvatarUrl', 'Users::fixAvatarUrl');
$routes->post('users/bindPhone', 'Users::bindPhone');
$routes->post('users/list', 'Users::list');
$routes->get('users/userTopic', 'Users::userTopic');
$routes->get('users/fixSystemId', 'Users::fixSystemId');
$routes->get('users/usersByUsername', 'Users::usersByUsername');
$routes->get('users/(:num)', 'Users::one/$1');

// ---------------------------------------------------------------------------
// Lives
// ---------------------------------------------------------------------------
$routes->get('lives/on', 'Lives::listOrderByPlanTs');
$routes->get('lives/time', 'Lives::listOrderByPlanTs');
$routes->get('lives/attendance', 'Lives::listOrderByAttendance');
$routes->get('lives/search', 'Lives::searchWithoutDetail');
$routes->get('lives/count', 'Lives::count');
$routes->get('lives/recommend', 'Lives::recommend');
$routes->get('lives/lastPrepare', 'Lives::lastPrepare');
$routes->get('lives/attended', 'Lives::attended');
$routes->get('lives/me', 'Lives::my');
$routes->get('lives/userLives', 'Lives::userLives');
$routes->get('lives/fixAttendanceCount', 'Lives::fixAttendanceCount');
$routes->get('lives/(:num)/alive', 'Lives::alive/$1');
$routes->get('lives/(:num)/end', 'Lives::end/$1');
$routes->get('lives/(:num)/begin', 'Lives::begin/$1');
$routes->get('lives/(:num)/submitReview', 'Lives::submitReview/$1');
$routes->get('lives/(:num)/publish', 'Lives::publish/$1');
$routes->get('lives/(:num)/finish', 'Lives::finish/$1');
$routes->get('lives/(:num)/error', 'Lives::error/$1');
$routes->get('lives/(:num)/users', 'Lives::attendedUsers/$1');
$routes->get('lives/(:num)/notify', 'Lives::notifyLiveStart/$1');
$routes->get('lives/(:num)/groupSend', 'Lives::groupSend/$1');
$routes->get('lives/(:num)/wait', 'Lives::setWait/$1');
$routes->get('lives/(:num)/setReview', 'Lives::setReview/$1');
$routes->get('lives/(:num)/notifyVideo', 'Lives::notifyVideo/$1');
$routes->get('lives/(:num)/import', 'Lives::import/$1');
$routes->get('lives/(:num)/notifyRelated', 'Lives::notifyLiveStartRecommend/$1');
$routes->get('lives/(:num)/notifyNewLive', 'Lives::notifyNewLive/$1');
$routes->get('lives/(:num)/card', 'Lives::invitationCard/$1');
$routes->post('lives/(:num)/topic', 'Lives::updateTopic/$1');
$routes->get('lives/(:num)', 'Lives::one/$1');
$routes->post('lives', 'Lives::create');
$routes->post('lives/(:num)', 'Lives::update/$1');

// ---------------------------------------------------------------------------
// Recorded Videos
// ---------------------------------------------------------------------------
$routes->get('lives/(:num)/recordedVideos', 'RecordedVideos::list/$1');
$routes->get('recordedVideos/convert', 'RecordedVideos::convert');
$routes->get('recordedVideos/replay', 'RecordedVideos::replay');

// ---------------------------------------------------------------------------
// Jobs
// ---------------------------------------------------------------------------
$routes->get('jobs/alive', 'Jobs::alive');

// ---------------------------------------------------------------------------
// Attendances
// ---------------------------------------------------------------------------
$routes->post('attendances', 'Attendances::create');
$routes->get('attendances/one', 'Attendances::one');
$routes->get('attendances/me', 'Attendances::myList');
$routes->get('attendances/lives/(:num)', 'Attendances::liveList/$1');
$routes->match(['get', 'post'], 'attendances/refund/(:num)', 'Attendances::refund/$1');
$routes->match(['get', 'post'], 'attendances/transfer', 'Attendances::transfer');
$routes->get('attendances/invites', 'Attendances::invites');

// ---------------------------------------------------------------------------
// Qrcodes
// ---------------------------------------------------------------------------
$routes->post('qrcodes', 'Qrcodes::scanQrcode');
$routes->get('qrcodes/scanned', 'Qrcodes::isQrcodeScanned');
$routes->get('qrcodes/gen', 'Qrcodes::png');
$routes->get('qrcodes/one', 'Qrcodes::qrcode');

// ---------------------------------------------------------------------------
// Rewards
// ---------------------------------------------------------------------------
$routes->post('rewards/notify', 'Rewards::notify');
$routes->post('rewards', 'Rewards::create');
$routes->get('lives/(:num)/rewards', 'Rewards::list/$1');

// ---------------------------------------------------------------------------
// Wechat
// ---------------------------------------------------------------------------
$routes->get('wechat/sign', 'Wechat::sign');
$routes->get('wechat/oauth', 'Wechat::oauth');
$routes->get('wechat/silentOauth', 'Wechat::silentOauth');
$routes->get('wechat/wxpayNotify', 'Wechat::wxpayNotify');
$routes->get('wechat/webOauth', 'Wechat::webOauth');
$routes->get('wechat/bind', 'Wechat::bind');
$routes->get('wechat/callback', 'Wechat::callback');
$routes->post('wechat/callback', 'Wechat::callbackPost');
$routes->get('wechat/appOauth', 'Wechat::appOauth');
$routes->get('wechat/isSubscribe', 'Wechat::isSubscribe');
$routes->get('wechat/createMenu', 'Wechat::createMenu');
$routes->get('wechat/menu', 'Wechat::menu');
$routes->get('wechat/fixAllSubscribe', 'Wechat::fixAllSubscribe');
$routes->get('wechat/qrcode', 'Wechat::qrcode');
$routes->get('wechat/addNews', 'Wechat::addNews');
$routes->get('wechat/uploadImg', 'Wechat::uploadImg');
$routes->get('wechat/sendMassMsg', 'Wechat::sendMassMsg');
$routes->get('wechat/group', 'Wechat::group');
$routes->post('wechat/login', 'Wechat::login');
$routes->post('wechat/registerByApp', 'Wechat::registerByApp');

// ---------------------------------------------------------------------------
// Live Hooks (SRS RTMP callbacks)
// ---------------------------------------------------------------------------
$routes->post('liveHooks/onPublish', 'LiveHooks::onPublish');
$routes->post('liveHooks/onUnPublish', 'LiveHooks::onUnPublish');
$routes->post('liveHooks/onDvr', 'LiveHooks::onDvr');

// ---------------------------------------------------------------------------
// Videos
// ---------------------------------------------------------------------------
$routes->get('videos/import', 'Videos::import');
$routes->get('videos/mp4Ready', 'Videos::mp4Ready');
$routes->get('lives/(:num)/videos', 'Videos::list/$1');
$routes->post('lives/(:num)/videos', 'Videos::create/$1');

// ---------------------------------------------------------------------------
// Live Views
// ---------------------------------------------------------------------------
$routes->post('liveViews', 'LiveViews::create');
$routes->get('liveViews/(:num)/end', 'LiveViews::end/$1');

// ---------------------------------------------------------------------------
// Staffs
// ---------------------------------------------------------------------------
$routes->post('staffs', 'Staffs::create');
$routes->get('staffs', 'Staffs::list');

// ---------------------------------------------------------------------------
// Charges
// ---------------------------------------------------------------------------
$routes->get('charges/one', 'Charges::one');
$routes->post('charges/remark', 'Charges::remark');
$routes->post('charges', 'Charges::create');
$routes->post('charges/appleCallback', 'Charges::appleCallback');

// ---------------------------------------------------------------------------
// Stats
// ---------------------------------------------------------------------------
$routes->get('stats', 'Stats::all');

// ---------------------------------------------------------------------------
// Applications
// ---------------------------------------------------------------------------
$routes->post('applications', 'Applications::create');
$routes->post('applications/(:num)', 'Applications::update/$1');
$routes->post('applications/(:num)/succeed', 'Applications::reviewSucceed/$1');
$routes->post('applications/(:num)/reject', 'Applications::reviewReject/$1');
$routes->get('applications/(:num)', 'Applications::one/$1');
$routes->get('applications/me', 'Applications::me');

// ---------------------------------------------------------------------------
// Packets
// ---------------------------------------------------------------------------
$routes->post('packets', 'Packets::create');
$routes->get('packets/(:alphanum)/grab', 'Packets::grab/$1');
$routes->get('packets/me', 'Packets::myPacket');
$routes->get('packets/meAll', 'Packets::meAll');
$routes->get('packets/sendPacket', 'Packets::sendPacket');
$routes->get('packets/(:alphanum)', 'Packets::one/$1');
$routes->get('packets/(:alphanum)/userPackets', 'Packets::allPacketsById/$1');

// ---------------------------------------------------------------------------
// Topics
// ---------------------------------------------------------------------------
$routes->post('topics', 'Topics::create');
$routes->get('topics', 'Topics::list');
$routes->get('topics/init', 'Topics::init');

// ---------------------------------------------------------------------------
// Subscribes
// ---------------------------------------------------------------------------
$routes->post('subscribes', 'Subscribes::create');
$routes->post('subscribes/del', 'Subscribes::del');

// ---------------------------------------------------------------------------
// Accounts
// ---------------------------------------------------------------------------
$routes->get('accounts/me', 'Accounts::me');
$routes->get('accounts/initIncome', 'Accounts::initIncome');

// ---------------------------------------------------------------------------
// Withdraws
// ---------------------------------------------------------------------------
$routes->post('withdraws', 'Withdraws::create');
$routes->get('withdraws', 'Withdraws::list');
$routes->get('withdraws/(:num)/agree', 'Withdraws::agree/$1');
$routes->post('withdraws/manual', 'Withdraws::createByManual');
$routes->get('withdraws/withdrawAnchor', 'Withdraws::withdrawAnchor');
$routes->get('withdraws/withdrawNonAnchor', 'Withdraws::withdrawNonAnchor');

// ---------------------------------------------------------------------------
// Files
// ---------------------------------------------------------------------------
$routes->get('files/uptoken', 'Files::uptoken');
$routes->get('files/wechatToQiniu', 'Files::wechatToQiniu');

// ---------------------------------------------------------------------------
// Wechat Groups
// ---------------------------------------------------------------------------
$routes->post('wechatGroups', 'WechatGroups::create');
$routes->get('wechatGroups', 'WechatGroups::list');
$routes->get('wechatGroups/one', 'WechatGroups::one');
$routes->get('wechatGroups/current', 'WechatGroups::current');
$routes->post('wechatGroups/update', 'WechatGroups::update');

// ---------------------------------------------------------------------------
// Shares
// ---------------------------------------------------------------------------
$routes->post('shares', 'Shares::create');

// ---------------------------------------------------------------------------
// Coupons
// ---------------------------------------------------------------------------
$routes->post('coupons', 'Coupons::create');
