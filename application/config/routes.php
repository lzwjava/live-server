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

// lives
$route['lives']['POST'] = 'lives/create';
$route['lives/on']['GET'] = 'lives/list';
$route['lives/(\d+)']['GET'] = 'lives/one/$1';
$route['lives/(\d+)']['POST'] = 'lives/update/$1';
$route['lives/(\d+)/alive']['GET'] = 'lives/alive/$1';
$route['lives/(\d+)/end']['GET'] = 'lives/end/$1';
$route['lives/(\d+)/begin']['GET'] = 'lives/begin/$1';
$route['lives/(\d+)/publish']['GET'] = 'lives/publish/$1';
$route['lives/lastPrepare']['GET'] = 'lives/lastPrepare';

$route['jobs/alive']['GET'] = 'jobs/alive';

// attendances
$route['attendances']['POST'] = 'attendances/create';
$route['attendances/one']['GET'] = 'attendances/one';
$route['attendances/me']['GET'] = 'attendances/myList';
$route['attendances/lives/(\d+)']['GET'] = 'attendances/liveList/$1';

// qrcodes
$route['qrcodes']['POST'] = 'qrcodes/scanQrcode';
$route['qrcodes/scanned']['GET'] = 'qrcodes/isQrcodeScanned';
$route['qrcodes/gen']['GET'] = 'qrcodes/png';

// alipay
$route['alipay/sign']['POST'] = 'alipay/sign';
$route['alipay/return']['POST'] = 'alipay/return';
$route['alipay/notify']['POST'] = 'alipay/notify';
