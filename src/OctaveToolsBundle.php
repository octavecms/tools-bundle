<?php

namespace Octave\ToolsBundle;

use Octave\ToolsBundle\DependencyInjection\Compiler\CacheAwareAdminCompiler;
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
    }
}
