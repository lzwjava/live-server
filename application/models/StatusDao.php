<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 7/28/16
 * Time: 10:41 PM
 */
class StatusDao extends BaseDao
{
    private $client;
    public $liveDao;
    private $ttl = 2 * 60;

    function __construct()
    {
        parent::__construct();
        $this->client = new Predis\Client([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 1
        ]);
        $this->load->model(LiveDao::class);
        $this->liveDao = new LiveDao();
    }

    private function key($key)
    {
        return 'status_' . $key;
    }

    private function alivesKey()
    {
        return $this->key('alives');
    }

    function open($id)
    {
        $ok = $this->client->hset($this->alivesKey(), $id, time());
        return $ok;
    }

    function alive($id)
    {
        if (!$this->client->hexists($this->alivesKey(), $id)) {
            return false;
        }
        $this->client->hset($this->alivesKey(), $id, time());
        return true;
    }

    function cleanStatus()
    {
        $alives = $this->client->hgetall($this->alivesKey());
        $total = 0;
        foreach ($alives as $id => $time) {
            $live = $this->liveDao->getLive($id);
            if (!$live) {
                logInfo("live not exists");
                continue;
            }
            $now = time();
            if ($now - $time > $this->ttl) {
                logInfo("end live because of ttl. liveId:" . $id);
                $this->endLive($id);
                $total++;
            }
        }
        logInfo("total count of end live:" . $total);
    }

    function endLive($id)
    {
        $ok = $this->liveDao->endLive($id);
        $delOk = $this->client->hdel($this->alivesKey(), $id);
        return $ok && $delOk;
    }

}
