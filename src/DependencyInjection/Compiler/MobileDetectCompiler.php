<?php

namespace Octave\ToolsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MobileDetectCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('octave.cache.listener');
        if ($container->hasAlias('mobile_detect.mobile_detector')) {
            $definition->addMethodCall('setMobileDetector', [new Reference('mobile_detect.mobile_detector')]);
        }
    }
}
