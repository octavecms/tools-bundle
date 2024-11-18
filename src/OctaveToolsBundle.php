<?php

namespace Octave\ToolsBundle;

use Octave\ToolsBundle\DependencyInjection\Compiler\CacheAwareAdminCompiler;
use Octave\ToolsBundle\DependencyInjection\Compiler\CacheExtensionCompiler;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Igor Lukashov <igor.lukashov@octavecms.com>
 */
class OctaveToolsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CacheAwareAdminCompiler());
        $container->addCompilerPass(new CacheExtensionCompiler());
    }
}
