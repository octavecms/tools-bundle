<?php

namespace VideInfra\ToolsBundle\Composer;

use Composer\Script\Event;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ScriptHandler
 * @package RequestBundle\Composer
 * @author Igor Lukashov <igor.lukashov@videinfra.com>
 */
class ScriptHandler
{
    /**
     * @param Event $event
     * @see Incenteev\\ParameterHandler\\ScriptHandler::buildParameters
     */
    public static function changeAssetsVersion(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();

        if (!isset($extras['incenteev-parameters'])) {
            throw new \InvalidArgumentException('The parameter handler needs to be configured through the extra.incenteev-parameters setting.');
        }

        $configs = $extras['incenteev-parameters'];

        if (!is_array($configs)) {
            throw new \InvalidArgumentException('The extra.incenteev-parameters setting must be an array or a configuration object.');
        }

        if (array_keys($configs) !== range(0, count($configs) - 1)) {
            $configs = array($configs);
        }

        foreach ($configs as $config) {

            if (!is_array($config)) {
                throw new \InvalidArgumentException('The extra.incenteev-parameters setting must be an array of configuration objects.');
            }

            $realFile = $config['file'];
            $yamlParser = new Parser();

            $event->getIO()->write(sprintf('<info>Changing assets version in the "%s" file</info>', $realFile));

            $values = $yamlParser->parse(file_get_contents($realFile));

            $values['parameters']['assets_version'] = static::getAssetVersion();
            file_put_contents($realFile, "# This file is auto-generated during the composer install\n" . Yaml::dump($values, 99));
        }
    }

    /**
     * @return int
     */
    private static function getAssetVersion()
    {
        return time();
    }
}