<?php

namespace Octave\ToolsBundle\Command;

use Octave\ToolsBundle\Util\RedisHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearRedisCacheCommand extends Command
{
    protected static $defaultName = 'octave:tools:clear-cache';

    private RedisHelper $redisHelper;

    public function __construct(RedisHelper $redisHelper)
    {
        $this->redisHelper = $redisHelper;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', InputOption::VALUE_OPTIONAL);
        $this->setDescription('Flushing redis cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');

        $this->redisHelper->flushAll($force);
        $output->writeln('<info>Clearing cache completed.</info>');

        return Command::SUCCESS;
    }
}
