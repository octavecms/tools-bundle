<?php

namespace Octave\ToolsBundle\EventListener;

use MobileDetectBundle\DeviceDetector\MobileDetector;
use Octave\ToolsBundle\Model\CacheExtensionInterface;
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
    const RESET_CACHE = '_cache_remove';

    private RedisHelper $redisHelper;
    private RouterInterface $router;
    private MobileDetector $mobileDetector;
    private string $locale;
    private string $env;
    private string $cachePrefix;
    private bool $enabledDefault = false;
    private array $extensions = [];

    public function __construct(
        RedisHelper $redisHelper,
        RouterInterface $router,
        MobileDetector $mobileDetector,
        string $locale,
        string $env,
        string $cachePrefix,
    )
    {
        $this->redisHelper = $redisHelper;
        $this->router = $router;
        $this->mobileDetector = $mobileDetector;
        $this->locale = $locale;
        $this->env = $env;
        $this->cachePrefix = $cachePrefix;

    }

    public function onKernelRequest(RequestEvent $event): void
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

        if ($request->get(self::RESET_CACHE)) {
            $cacheData = $this->redisHelper->get($hash, $key);
            if (!is_array($cacheData)) {
                $cacheData = [
                    'content' => null,
                    'expired' => false,
                    'count' => 0,
                    'url' => $request->getUri(),
                ];
            }

            if (!array_key_exists('content', $cacheData) || !array_key_exists('expired', $cacheData) || !array_key_exists('count', $cacheData) || !array_key_exists('url', $cacheData)) {
                $cacheData = [
                    'content' => null,
                    'expired' => false,
                    'count' => 0,
                    'url' => $request->getUri(),
                ];
            }

            $cacheData['content'] = null;

            $this->redisHelper->set($hash, $key, $cacheData);
        }

        $cacheData = $this->redisHelper->get($hash, $key);
        if (!is_array($cacheData)) {
            $cacheData = [
                'content' => $cacheData,
                'expired' => false,
                'count' => 0,
                'url' => $request->getUri(),
            ];
        }

        if (!array_key_exists('content', $cacheData) || !array_key_exists('expired', $cacheData) || !array_key_exists('count', $cacheData) || !array_key_exists('url', $cacheData)) {
            $cacheData = [
                'content' => null,
                'expired' => false,
                'count' => 0,
                'url' => $request->getUri(),
            ];
        }

        if ($cacheData['content']) {
            $cacheData['count']++;

            $response = new Response();
            $response->headers->add([
                'X-Octave-Cache' => 'HIT',
            ]);
            $response->setContent($cacheData['content']);

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

        $response = $event->getResponse();
        if (!$response->isSuccessful()) {
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

        $cacheData = $this->redisHelper->get($hash, $key);

        if ($cacheData) {
            if (!is_array($cacheData)) {
                $cacheData = [
                    'content' => $cacheData,
                    'expired' => false,
                    'count' => 0,
                    'url' => $request->getUri(),
                ];
            }

            if (!array_key_exists('content', $cacheData) || !array_key_exists('expired', $cacheData) || !array_key_exists('count', $cacheData) || !array_key_exists('url', $cacheData)) {
                $cacheData = [
                    'content' => null,
                    'expired' => false,
                    'count' => 0,
                    'url' => $request->getUri(),
                ];
            }

            if (null === $cacheData['content']) {
                $cacheData['content'] = $event->getResponse()->getContent();
            }

            $cacheData['count']++;
        } else {
            $cacheData = [
                'content' => $event->getResponse()->getContent(),
                'expired' => false,
                'count' => 0,
                'url' => $request->getUri(),
            ];
        }

        $this->redisHelper->set($hash, $key, $cacheData);
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
        $isMobile = $this->mobileDetector->isMobile() ? '_mobile' : '';

        $mode = $options['cache_mode'] ?? self::MODE_QUERY;

        if ($mode === self::MODE_PATH) {
            $key = md5($request->getPathInfo() . $isMobile);
        } else {
            $path = $request->getPathInfo();
            $query = $request->query->all();
            $params = [];

            if ($options['cache_query_include'] ?? []) {
                foreach ($query as $key => $value) {
                    if (in_array($key, $options['cache_query_include'])) {
                        $params[$key] = $value;
                    }
                }
            } else if ($options['cache_query_exclude'] ?? []) {

                $exclude = $options['cache_query_exclude'] ?? [];
                $exclude[] = self::RESET_CACHE;

                foreach ($query as $key => $value) {
                    if (!in_array($key, $options['cache_query_exclude'])) {
                        $params[$key] = $value;
                    }
                }
            } else {
                foreach ($query as $key => $value) {
                    if ($key !== self::RESET_CACHE) {
                        $params[$key] = $value;
                    }
                }
            }

            $key = md5($path.$isMobile.($params ? '?'.http_build_query($params) : ''));
        }

        foreach ($this->extensions as $extension) {
            $extension->apply($key, $options, $request);
        }

        return $key;
    }

    public function addExtension(CacheExtensionInterface $extension): void
    {
        $this->extensions[] = $extension;
    }

}
