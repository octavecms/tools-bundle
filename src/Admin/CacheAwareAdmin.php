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

    protected function postUpdate(object $object): void
    {
        $this->resetCache();
    }

    protected function postPersist(object $object): void
    {
        $this->resetCache();
    }

    protected function postRemove(object $object): void
    {
        $this->resetCache();
    }
}
