<?php

namespace Octave\ToolsBundle\DependencyInjection\Compiler;

use Octave\ToolsBundle\Admin\CacheAwareAdminInterface;
use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CacheAwareAdminCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $admins = $container->findTaggedServiceIds(TaggedAdminInterface::ADMIN_TAG);
        foreach ($admins as $id => $tags) {
            $definition = $container->getDefinition($id);

            $class = $definition->getClass();

            if (!class_exists($class)) {
                $class = str_replace('%', '', $class);
                $class = $container->getParameter($class);
            }

            if (class_exists($class) && in_array(CacheAwareAdminInterface::class, class_implements($class))) {
                $definition->addMethodCall('setRedisHelper', [new Reference('octave.redis.helper')]);
            }
        }
    }
}
