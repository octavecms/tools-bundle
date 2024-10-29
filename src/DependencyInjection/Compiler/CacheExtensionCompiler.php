<?php

namespace Octave\ToolsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CacheExtensionCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('octave.cache.listener')) {
            return;
        }

        $definition = $container->findDefinition('octave.cache.listener');
        $taggedServices = $container->findTaggedServiceIds('octave.cache.extension');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addExtension', [new Reference($id)]);
        }
    }
}
