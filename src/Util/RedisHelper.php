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

    public function get(string $hash, string $key, bool $usePrefix = true)
    {
        if ($usePrefix) {
            $hash = $this->cachePrefix . ':' . $hash;
        }

        $cache = $this->redis->hGet($hash, $key);
        return $cache ? json_decode($cache, true): null;
    }

    public function set(string $hash, string $key, $value, bool $usePrefix = true): void
    {
        if ($usePrefix) {
            $hash = $this->cachePrefix . ':' . $hash;
        }

        $this->redis->hSet($hash, $key, json_encode($value));
    }

    public function remove(string $hash, $usePrefix = false)
    {
        if ($usePrefix) {
            $hash = $this->cachePrefix . ':' . $hash;
        }

        $keys = $this->getKeysByHash($hash);
        foreach ($keys as $key => $value) {
            $cacheData = json_decode($value, true);
            $cacheData = is_array($cacheData) ? $cacheData : [];
            $cacheData['expired'] = true;
            $this->set($hash, $key, $cacheData, false);
        }
    }
    public function flushAll($force = false)
    {
        foreach ($this->getAll() as $hash) {
            if ($force) {
                $this->redis->del($hash);
            } else {
                $this->remove($hash);
            }
        }
    }

    public function getKeysByHash(string $hash)
    {
        return $this->redis->hGetAll($hash);
    }

    public function getAll()
    {
        return $this->redis->keys($this->cachePrefix . '*');
    }
}
