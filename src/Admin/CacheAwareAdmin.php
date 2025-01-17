<?php

namespace Octave\ToolsBundle\Admin;

use Octave\ToolsBundle\Util\RedisHelper;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Symfony\Component\Routing\RouterInterface;

class CacheAwareAdmin extends AbstractAdmin implements CacheAwareAdminInterface
{
    protected array $cacheNames = [];

    protected RedisHelper $redisHelper;

    protected RouterInterface $router;

    public function setRedisHelper(RedisHelper $redisHelper)
    {
        $this->redisHelper = $redisHelper;
    }

    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    protected function resetCache(): void
    {
        $routeConfigs = $this->getRoutesConfig($this->cacheNames);

        foreach ($this->cacheNames as $name) {
            $routeConfig = $routeConfigs[$name] ?? null;

            if ($this->shouldDeleteCache($routeConfig)) {
                $this->redisHelper->forceDelete($name, true);
            } else {
                $this->redisHelper->remove($name, true);
            }
        }
    }

    protected function getRoutesConfig(array $routeNames): array
    {
        $configs = [];
        $routeCollection = $this->router->getRouteCollection();

        foreach ($routeNames as $routeName) {
            foreach ($routeCollection as $name => $route) {
                if (str_ends_with($name, $routeName)) {
                    $configs[$routeName] = $route->getOptions();
                    break;
                }
            }
        }

        return $configs;
    }
    protected function shouldDeleteCache(?array $config): bool
    {
        if (!$config) {
            return false;
        }

        return isset($config['cache_expire']) && $config['cache_expire'] === false;
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
