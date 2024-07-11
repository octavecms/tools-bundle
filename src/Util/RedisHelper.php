<?php

namespace Octave\ToolsBundle\Util;

class RedisHelper
{
    private \Redis $redis;
    private string $cachePrefix;

    public function __construct(\Redis $redis, string $cachePrefix)
    {
        $this->redis = $redis;
        $this->cachePrefix = $cachePrefix;
    }

    public function get(string $hash, string $key)
    {
        $cache = $this->redis->hGet($this->cachePrefix . ': ' . $hash, $key);
        return $cache ? json_decode($cache, true): null;
    }

    public function set(string $hash, string $key, $value): void
    {
        $this->redis->hSet($this->cachePrefix . ': ' . $hash, $key, json_encode($value));
    }

    public function remove(string $hash)
    {
        $this->redis->del($this->cachePrefix . ': ' . $hash);
    }

    public function flushAll()
    {
        $cache = $this->redis->keys($this->cachePrefix . '*');
        foreach ($cache as $hash) {
            $this->redis->del($hash);
        }
    }
}
