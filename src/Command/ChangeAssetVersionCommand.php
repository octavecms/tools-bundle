<?php

namespace Octave\ToolsBundle\Command;

use Dotenv\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeAssetVersionCommand extends Command
{
    protected function configure()
    {
        $this->setName('octave:tools:assets-version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dotenv = Dotenv::create(__DIR__, 'local');
        $result = $dotenv->load();


    }


}