<?php

namespace Octave\ToolsBundle\Command;

use Octave\ToolsBundle\Util\RedisHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoRedisCacheCommand extends Command
{
    protected static $defaultName = 'octave:tools:info';

    private RedisHelper $redisHelper;

    public function __construct(RedisHelper $redisHelper)
    {
        $this->redisHelper = $redisHelper;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('hash', InputArgument::REQUIRED, 'Cache hash');
        $this->addArgument('key', InputArgument::OPTIONAL, 'Cache key');
        $this->setDescription('Get cache info');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hash = $input->getArgument('hash');
        $key = $input->getArgument('key');

        $keys = $key ? [$key] : $this->redisHelper->getKeysByHash($hash);
        foreach ($keys as $key) {
            $cache = $this->redisHelper->get($hash, $key);

            $output->writeln(sprintf('Hash: <info>%s</info>, Key: <info>%s</info>', $hash, $key));
            $output->writeln(sprintf('Expired: <info>%s</info>', $cache['expired']));
            $output->writeln(sprintf('Count: <info>%s</info>', $cache['count']));
            $output->writeln(sprintf('Length: <info>%s</info>', strlen($cache['content'])));

            $output->writeln('----------------------------------');
        }

        return Command::SUCCESS;
    }
}
