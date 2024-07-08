<?php

namespace Octave\ToolsBundle\Util;

class RedisHelper
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $hash, string $key)
    {
        $cache = $this->redis->hGet($hash, $key);
        return $cache ? json_decode($cache, true): null;
    }

    public function set(string $hash, string $key, $value): void
    {
        $this->redis->hSet($hash, $key, json_encode($value));
    }

    public function remove(string $hash)
    {
        $this->redis->del($hash);
    }

    public function flushAll()
    {
        $this->redis->flushAll();
    }
}
