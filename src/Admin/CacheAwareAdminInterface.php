<?php

namespace Octave\ToolsBundle\Admin;

use Octave\ToolsBundle\Util\RedisHelper;

interface CacheAwareAdminInterface
{
    public function setRedisHelper(RedisHelper $redisHelper);
}
