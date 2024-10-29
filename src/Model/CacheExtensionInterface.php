<?php

namespace Octave\ToolsBundle\Model;

use Symfony\Component\HttpFoundation\Request;

interface CacheExtensionInterface
{
    public function apply(string &$key, array $options, Request $request): void;
}
