<?php

namespace Octave\ToolsBundle\Admin;

use Octave\ToolsBundle\Util\RedisHelper;
use Sonata\AdminBundle\Admin\AbstractAdmin;

class CacheAwareAdmin extends AbstractAdmin implements CacheAwareAdminInterface
{
    protected array $cacheNames = [];

    protected RedisHelper $redisHelper;

    public function setRedisHelper(RedisHelper $redisHelper)
    {
        $this->redisHelper = $redisHelper;
    }

    protected function resetCache(): void
    {
        foreach ($this->cacheNames as $name) {
            $this->redisHelper->remove($name, true);
        }
    }

    public function postUpdate($object)
    {
        $this->resetCache();
    }

    public function postPersist($object)
    {
        $this->resetCache();
    }

    public function postRemove($object)
    {
        $this->resetCache();
    }
}
