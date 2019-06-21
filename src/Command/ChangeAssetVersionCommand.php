<?php

namespace Octave\ToolsBundle\Command;

use Dotenv\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeAssetVersionCommand extends Command
{
    const KEY = 'ASSETS_VERSION';

    /**
     * @var string
     */
    protected static $defaultName = 'octave:tools:assets-version';

    /**
     * @var string
     */
    private $projectDir;

    /**
     * ChangeAssetVersionCommand constructor.
     * @param $rootDir
     */
    public function __construct($rootDir)
    {
        parent::__construct();
        $this->projectDir = $rootDir;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localEnvPath = sprintf('%s/%s', $this->projectDir, '.env.local');

        if (file_exists($localEnvPath)) {

            $dotenv = Dotenv::create($this->projectDir , '.env.local');
            $parameters = $dotenv->load();

            $parameters[self::KEY] = time();
        }
        else {

            $parameters = [
                self::KEY => time()
            ];
        }

        $output = [];

        foreach ($parameters as $name => $value) {
            if (strpos($value, ' ') !== false) {
                $output[] = sprintf("%s='%s'", $name, $value);
            }
            else {
                $output[] = sprintf('%s=%s', $name, $value);
            }
        }

        file_put_contents($localEnvPath, implode(PHP_EOL, $output));
    }
}