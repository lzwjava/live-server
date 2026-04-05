<?php

/**
 * CI3 get_instance() compatibility for CI4.
 *
 * Include in public/index.php before Boot::bootWeb().
 *
 * After inclusion, CI3-era library code like:
 *   $ci = get_instance();
 *   $ci->load->model('UserDao');
 *   $this->userDao = $ci->userDao;
 *
 * Will work in CI4 context.
 */

if (!function_exists('get_instance')) {
    function &get_instance(): object
    {
        static $proxy = null;
        if ($proxy === null) {
            $proxy = new CI3_Loader_Proxy();
        }
        return $proxy;
    }
}

/**
 * Sub-object returned by $ci->load.
 * Provides ->model() and ->library() methods (CI3-style).
 */
class CI3_Loader
{
    private CI3_Loader_Proxy $parent;

    public function __construct(CI3_Loader_Proxy $parent)
    {
        $this->parent = $parent;
    }

    public function model(string $class, array $params = []): CI3_Loader
    {
        $this->parent->loadThis($class);
        return $this;
    }

    public function library(string $class, array $params = []): CI3_Loader
    {
        $this->parent->loadThis($class);
        return $this;
    }
}

/**
 * Minimal CI3 $this->load proxy attached to get_instance().
 * Handles ->load->model() / ->load->library() and attaches instances
 * back onto the proxy so $ci->className works after loading.
 */
class CI3_Loader_Proxy
{
    /** @var object[] */
    private array $cache = [];

    /** @var CI3_Loader|null */
    private ?CI3_Loader $loader = null;

    /**
     * Magic getter — allows $ci->load (returns the loader sub-object).
     * Also supports accessing loaded classes as properties (CI3 style: $ci->userDao).
     */
    public function __get(string $name): mixed
    {
        if ($name === 'load') {
            return $this->load();
        }
        // Support $ci->{$loadedClass} style access (CI3 legacy)
        $key = strtolower($name);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        // Lazy-load if accessed as property
        $this->loadThis($name);
        return $this->cache[$key] ?? null;
    }

    /**
     * Access $ci->load — returns a CI3_Loader instance.
     * @return CI3_Loader
     */
    public function load(): CI3_Loader
    {
        if ($this->loader === null) {
            $this->loader = new CI3_Loader($this);
        }
        return $this->loader;
    }

    /**
     * Load and instantiate a class by name.
     * Attach it to $this->{$lowercaseClassName} so $ci->className works.
     */
    public function loadThis(string $class): void
    {
        $key = strtolower($class);
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->instantiate($class);
        }
        $this->{$key} = $this->cache[$key];
    }

    private function instantiate(string $class): object
    {
        $map = [
            // Models — map class name (lowercase) to fully-qualified name
            'snsuserdao'    => 'App\\Models\\SnsUserDao',
            'userdao'       => 'App\\Models\\UserDao',
            'wxappdao'      => 'App\\Models\\WxAppDao',
            'wxdao'         => 'App\\Models\\WxDao',
            'wxsessiondao'  => 'App\\Models\\WxSessionDao',
            'livedao'       => 'App\\Models\\LiveDao',
            'chargedao'     => 'App\\Models\\ChargeDao',
            'paynotifydao'  => 'App\\Models\\PayNotifyDao',
            'applicationdao'=> 'App\\Models\\ApplicationDao',
            'incomedao'    => 'App\\Models\\IncomeDao',
            'withdrawdao'  => 'App\\Models\\WithdrawDao',
            'rewarddao'    => 'App\\Models\\RewardDao',
            'packetdao'    => 'App\\Models\\PacketDao',
            'attendancedao' => 'App\\Models\\AttendanceDao',
            'messagedao'   => 'App\\Models\\MessageDao',
            'videodao'     => 'App\\Models\\VideoDao',
            'jobdao'       => 'App\\Models\\JobDao',
            'jobhelperdao' => 'App\\Models\\JobHelperDao',
            'coupondao'    => 'App\\Models\\CouponDao',
            'qinuilive'    => 'App\\Models\\QiniuLive',
            // Libraries
            'wechatclient'   => 'App\\Libraries\\WeChatClient',
            'wechatplatform' => 'App\\Libraries\\WeChatPlatform',
            'jssdk'          => 'App\\Libraries\\JSSDK',
            'leancloud'      => 'App\\Libraries\\LeanCloud',
            'pay'            => 'App\\Libraries\\Pay',
            'sms'            => 'App\\Libraries\\Sms',
            'wxpay'          => 'App\\Libraries\\WxPay',
            'wxpaycallback'  => 'App\\Libraries\\WxPayCallback',
            'alipay'         => 'App\\Libraries\\Alipay',
        ];

        $key = strtolower($class);

        // 1. Check explicit map
        if (isset($map[$key]) && class_exists($map[$key])) {
            $fqcn = $map[$key];
            $rc = new ReflectionClass($fqcn);
            if ($rc->getConstructor()?->getNumberOfRequiredParameters() === 0) {
                return $rc->newInstance();
            }
        }

        // 2. Fallback: try App\Models\ClassName
        $fallback = "App\\Models\\{$class}";
        if (class_exists($fallback)) {
            $rc = new ReflectionClass($fallback);
            if ($rc->getConstructor()?->getNumberOfRequiredParameters() === 0) {
                return new $fallback();
            }
        }

        // 3. Fallback: try App\Libraries\ClassName
        $libFallback = "App\\Libraries\\{$class}";
        if (class_exists($libFallback)) {
            $rc = new ReflectionClass($libFallback);
            if ($rc->getConstructor()?->getNumberOfRequiredParameters() === 0) {
                return new $libFallback();
            }
        }

        // 4. Fallback: global class
        if (class_exists($class)) {
            return new $class();
        }

        throw new RuntimeException(
            "CI3_Compat: cannot instantiate '{$class}'"
        );
    }
}
