<?php

namespace Octave\ToolsBundle\EventListener;

use Octave\ToolsBundle\Util\RedisHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class CacheListener implements EventSubscriberInterface
{
    const MODE_PATH = 'path';
    const MODE_QUERY = 'query';

    private RedisHelper $redisHelper;
    private RouterInterface $router;
    private string $locale;
    private string $env;
    private bool $enabledDefault = false;

    public function __construct(
        RedisHelper $redisHelper,
        RouterInterface $router,
        string $locale,
        string $env,
    )
    {
        $this->redisHelper = $redisHelper;
        $this->router = $router;
        $this->locale = $locale;
        $this->env = $env;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->env !== 'prod') {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            return;
        }

        $route = $this->getRoute($request);
        if (!$route) {
            return;
        }

        $options = $route->getOptions();
        $cacheEnabled = $options['cache_enabled'] ?? $this->enabledDefault;
        if (!$cacheEnabled) {
            return;
        }

        $routeName = $request->attributes->get('_route');
        $hash = $options['cache_namespace'] ?? $routeName;
        $key = $this->getCacheKey($request, $options);

        if ($request->get('_cache_remove')) {
            $this->redisHelper->set($hash, $key, null);
        }

        if ($this->redisHelper->get($hash, $key)) {
            $response = new Response();
            $response->setContent($this->redisHelper->get($hash, $key));

            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->env !== 'prod') {
            return;
        }

        $request = $event->getRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            return;
        }

        $route = $this->getRoute($request);
        if (!$route) {
            return;
        }

        $options = $route->getOptions();
        $cacheEnabled = $options['cache_enabled'] ?? $this->enabledDefault;
        if (!$cacheEnabled) {
            return;
        }

        $routeName = $request->attributes->get('_route');
        $hash = $options['cache_namespace'] ?? $routeName;
        $key = $this->getCacheKey($request, $options);

        $this->redisHelper->set($hash, $key, $event->getResponse()->getContent());
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
            KernelEvents::RESPONSE => array(array('onKernelResponse', 15)),
        );
    }

    private function getRoute(Request $request): ?Route
    {
        $routeName = $request->attributes->get('_route');
        $route = null;

        if ($routeName) {
            $route = $this->router->getRouteCollection()->get($routeName);
            if (!$route) {
                $route = $this->router->getRouteCollection()->get(sprintf('%s__RG__%s', $this->locale, $routeName));
            }
        }

        return $route;
    }

    private function getCacheKey(Request $request, array $options): string
    {
        $mode = $options['cache_mode'] ?? self::MODE_QUERY;

        if ($mode === self::MODE_PATH) {
            $key = md5($request->getPathInfo());
        } else {
            $path = $request->getPathInfo();
            $query = $request->query->all();
            $params = [];

            if ($options['cache_query_include'] ?? []) {
                foreach ($query as $key => $value) {
                    if (in_array($key, $options['cache_query_include'])) {
                        $params[] = sprintf('%s=%s', $key, $value);
                    }
                }
            } else if ($options['cache_query_exclude'] ?? []) {
                foreach ($query as $key => $value) {
                    if (!in_array($key, $options['cache_query_exclude'])) {
                        $params[] = sprintf('%s=%s', $key, $value);
                    }
                }
            } else {
                foreach ($query as $key => $value) {
                    $params[] = sprintf('%s=%s', $key, $value);
                }
            }

            $key = md5($path.'?'.implode('&', $params));
        }

        return $key;
    }
}
