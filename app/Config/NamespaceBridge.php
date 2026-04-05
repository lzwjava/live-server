<?php

/**
 * Namespace bridge — maps App\Libraries\* → App\Models\* for all DAO classes.
 *
 * Libraries in App\Libraries namespace (like Pay, WeChatAppClient) do
 * `new SomeDao()` which PHP resolves as App\Libraries\SomeDao.
 * Models declare App\Models\SomeDao. This bridge creates class aliases
 * so App\Libraries\SomeDao = App\Models\SomeDao.
 *
 * Include AFTER Composer's autoloader is registered (after vendor/autoload.php).
 */

use Composer\Autoload\ComposerStaticInit3eddc5a9c36e8f36c10aee1e9ae8d8e8;

$modelsDir = __DIR__ . '/../Models/';
$models = [
    'AccountDao', 'ApplicationDao', 'AttendanceDao', 'ChargeDao',
    'CouponDao', 'IncomeDao', 'JobDao', 'JobHelperDao', 'LiveDao',
    'LiveViewDao', 'PacketDao', 'PayNotifyDao', 'QiniuDao',
    'RecordedVideoDao', 'RewardDao', 'ShareDao', 'SnsUserDao',
    'StaffDao', 'StatusDao', 'SubscribeDao', 'TopicDao',
    'TransactionDao', 'UserDao', 'UserPacketDao', 'VideoDao',
    'WechatEventsDao', 'WechatGroupDao', 'WithdrawDao', 'WxAppDao',
    'WxDao', 'WxSessionDao',
];

foreach ($models as $model) {
    $fqcn = "App\\Models\\{$model}";
    $alias = "App\\Libraries\\{$model}";
    if (!class_exists($alias, false) && class_exists($fqcn, false)) {
        class_alias($fqcn, $alias);
    }
}
