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
            if ($definition->getClass() instanceof CacheAwareAdminInterface) {
                $definition->setMethodCalls('setRedisHelper', new Reference('octave.redis.helper'));
            }
        }
    }
}
