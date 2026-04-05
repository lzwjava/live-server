<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * -------------------------------------------------------------------
 * AUTOLOADER CONFIGURATION
 * -------------------------------------------------------------------
 *
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 *
 * NOTE: If you use an identical key in $psr4 or $classmap, then
 *       the values in this file will overwrite the framework's values.
 *
 * NOTE: This class is required prior to Autoloader instantiation,
 *       and does not extend BaseConfig.
 */
class Autoload extends AutoloadConfig
{
    /**
     * -------------------------------------------------------------------
     * Namespaces
     * -------------------------------------------------------------------
     * This maps the locations of any namespaces in your application to
     * their location on the file system. These are used by the autoloader
     * to locate files the first time they have been instantiated.
     *
     * The 'Config' (APPPATH . 'Config') and 'CodeIgniter' (SYSTEMPATH) are
     * already mapped for you.
     *
     * You may change the name of the 'App' namespace if you wish,
     * but this should be done prior to creating any namespaced classes,
     * else you will need to modify all of those classes for this to work.
     *
     * @var array<string, list<string>|string>
     */
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
        'App\\Libraries' => APPPATH . 'Libraries',
    ];

    /**
     * -------------------------------------------------------------------
     * Class Map
     * -------------------------------------------------------------------
     * The class map provides a map of class names and their exact
     * location on the drive. Classes loaded in this manner will have
     * slightly faster performance because they will not have to be
     * searched for within one or more directories as they would if they
     * were being autoloaded through a namespace.
     *
     * Prototype:
     *   $classmap = [
     *       'MyClass'   => '/path/to/class/file.php'
     *   ];
     *
     * @var array<string, string>
     */
    public $classmap = [
        // Global class names (used by controllers via $this->load or direct instantiation)
        'JSSDK'           => APPPATH . 'Libraries/JSSDK.php',
        'WeChatPlatform'  => APPPATH . 'Libraries/WeChatPlatform.php',
        'Pay'             => APPPATH . 'Libraries/Pay.php',
        'Sms'             => APPPATH . 'Libraries/Sms.php',
        'LeanCloud'       => APPPATH . 'Libraries/LeanCloud.php',
        'Alipay'          => APPPATH . 'Libraries/Alipay.php',
        'WxPay'           => APPPATH . 'Libraries/WxPay.php',
        'WxPayCallback'   => APPPATH . 'Libraries/WxPayCallback.php',
        'WxDao'           => APPPATH . 'Models/WxDao.php',
        'SnsUserDao'      => APPPATH . 'Models/SnsUserDao.php',
        'QiniuDao'        => APPPATH . 'Models/QiniuDao.php',
        'UserDao'         => APPPATH . 'Models/UserDao.php',
        'LiveDao'         => APPPATH . 'Models/LiveDao.php',
        'ChargeDao'       => APPPATH . 'Models/ChargeDao.php',
        'PayNotifyDao'    => APPPATH . 'Models/PayNotifyDao.php',
        'ApplicationDao'  => APPPATH . 'Models/ApplicationDao.php',
        'IncomeDao'       => APPPATH . 'Models/IncomeDao.php',
        'WithdrawDao'     => APPPATH . 'Models/WithdrawDao.php',
        'WxSessionDao'    => APPPATH . 'Models/WxSessionDao.php',
        'WxAppDao'        => APPPATH . 'Models/WxAppDao.php',
        'RewardDao'       => APPPATH . 'Models/RewardDao.php',
        'PacketDao'       => APPPATH . 'Models/PacketDao.php',
        'AttendanceDao'   => APPPATH . 'Models/AttendanceDao.php',
        'MessageDao'      => APPPATH . 'Models/MessageDao.php',

        // Fully-namespaced entries for migrated CI3 code that does "new ClassName()"
        // inside App\Models namespace — PHP resolves these as App\Models\ClassName first
        'App\Models\JSSDK'            => APPPATH . 'Libraries/JSSDK.php',
        'App\Models\WeChatPlatform'  => APPPATH . 'Libraries/WeChatPlatform.php',
        'App\Models\Pay'              => APPPATH . 'Libraries/Pay.php',
        'App\Models\Sms'              => APPPATH . 'Libraries/Sms.php',
        'App\Models\LeanCloud'        => APPPATH . 'Libraries/LeanCloud.php',
        'App\Models\Alipay'           => APPPATH . 'Libraries/Alipay.php',
        'App\Models\WxPay'            => APPPATH . 'Libraries/WxPay.php',
        'App\Models\WxPayCallback'    => APPPATH . 'Libraries/WxPayCallback.php',
        // Fully-namespaced for classes called from Controllers namespace
        'App\Controllers\WeChatAppClient' => APPPATH . 'Libraries/WeChatAppClient.php',
        'App\Controllers\WxBizDataCrypt'  => APPPATH . 'Libraries/wxencrypt/WxBizDataCrypt.php',
        // CI3-style library classes that reference models without namespace
        'App\Libraries\ChargeDao'     => APPPATH . 'Models/ChargeDao.php',
        'App\Libraries\PayNotifyDao'   => APPPATH . 'Models/PayNotifyDao.php',
        'App\Libraries\WxAppDao'       => APPPATH . 'Models/WxAppDao.php',
        'App\Libraries\WxDao'          => APPPATH . 'Models/WxDao.php',
        'App\Libraries\WxSessionDao'   => APPPATH . 'Models/WxSessionDao.php',
        'App\Libraries\SnsUserDao'     => APPPATH . 'Models/SnsUserDao.php',
        'App\Libraries\UserDao'        => APPPATH . 'Models/UserDao.php',
        'App\Libraries\LiveDao'        => APPPATH . 'Models/LiveDao.php',
        'App\Libraries\WxPay'          => APPPATH . 'Libraries/WxPay.php',
        'App\Libraries\WxPayCallback'  => APPPATH . 'Libraries/WxPayCallback.php',
        'App\Libraries\Alipay'         => APPPATH . 'Libraries/Alipay.php',
        'App\Libraries\WxBizDataCrypt' => APPPATH . 'Libraries/wxencrypt/WxBizDataCrypt.php',
    ];

    /**
     * -------------------------------------------------------------------
     * Files
     * -------------------------------------------------------------------
     * The files array provides a list of paths to __non-class__ files
     * that will be autoloaded. This can be useful for bootstrap operations
     * or for loading functions.
     *
     * Prototype:
     *   $files = [
     *       '/path/to/my/file.php',
     *   ];
     *
     * @var list<string>
     */
    public $files = [];

    /**
     * -------------------------------------------------------------------
     * Helpers
     * -------------------------------------------------------------------
     * Prototype:
     *   $helpers = [
     *       'form',
     *   ];
     *
     * @var list<string>
     */
    public $helpers = [];
}
