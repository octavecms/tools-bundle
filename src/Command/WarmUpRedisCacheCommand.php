<?php

namespace Octave\ToolsBundle\Command;

use Octave\ToolsBundle\EventListener\CacheListener;
use Octave\ToolsBundle\Util\RedisHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WarmUpRedisCacheCommand extends Command
{
    protected static $defaultName = 'octave:tools:cache-warm-up';

    private RedisHelper $redisHelper;
    private HttpClientInterface $client;

    public function __construct(RedisHelper $redisHelper, HttpClientInterface $client)
    {
        $this->redisHelper = $redisHelper;
        $this->client = $client;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('minCount', InputArgument::OPTIONAL, 'Minimal cache hit count');
        $this->setDescription('Warm-up redis cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $minCount = $input->getArgument('minCount');

        $hashes = $this->redisHelper->getAll();

        foreach ($hashes as $hash) {
            $keys = $this->redisHelper->getKeysByHash($hash);
            foreach ($keys as $key => $value) {

                if ($output->isVerbose()) {
                    $output->writeln(sprintf('Hash: %s, key: %s', $hash, $key));
                }

                $cacheData = json_decode($value, true);

                if (!is_array($cacheData)) {
                    continue;
                }

                if (!array_key_exists('content', $cacheData) || !array_key_exists('expired', $cacheData) || !array_key_exists('count', $cacheData) || !array_key_exists('url', $cacheData)) {
                    continue;
                }

                if ($output->isVerbose()) {
                    $output->writeln(sprintf('Expired: %s', $cacheData['expired']));
                }

                if (false === $cacheData['expired']) {
                    continue;
                }

                if ($minCount && $minCount > $cacheData['count']) {
                    continue;
                }

                $url = $cacheData['url'];

                if ($output->isVerbose()) {
                    $output->writeln(sprintf('URL: %s', $url));
                }

                $parsedUrl = parse_url($url);

                $params = [];
                if (isset($parsedUrl['query'])) {
                    $query = $parsedUrl['query'];
                    parse_str($query, $params);
                }
                $params[CacheListener::RESET_CACHE] = 1;
                $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];

                $url = $baseUrl . '?' . http_build_query($params);

                $response = $this->client->request('GET', $url);

                $content = $cacheData['content'];
                if ($output->isVerbose()) {
                    $output->writeln(sprintf('Status code: %s', $response->getStatusCode()));
                }
                if (200 === $response->getStatusCode()) {
                    $content = $response->getContent();
                    $cacheData['expired'] = false;
                }
                $cacheData['content'] = $content;

                $this->redisHelper->set($hash, $key, $cacheData, false);
            }
        }

        $output->writeln('<info>Warm-up cache completed.</info>');

        return 0;
    }
}
