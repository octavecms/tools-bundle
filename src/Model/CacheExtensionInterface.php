<?php

namespace Octave\ToolsBundle\Model;

interface CacheExtensionInterface
{
    public function apply(string $key, array $options, Request $request): void;
}
