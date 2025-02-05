<?php

namespace Octave\ToolsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;

class PurgeCloudflareCacheCommand extends Command
{
    protected static $defaultName = 'octave:tools:purge-cloudflare-cache';

    private string $apiToken;
    private string $zoneId;

    public function __construct(ParameterBagInterface $params)
    {
        parent::__construct();

        $this->apiToken = $params->get('cf_api_token');
        $this->zoneId = $params->get('zone_id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $purgeUrl = sprintf('https://api.cloudflare.com/client/v4/zones/%s/purge_cache', $this->zoneId);

        $client = HttpClient::create();

        try {
            $response = $client->request('POST', $purgeUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'purge_everything' => true,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray();

            if ($statusCode === 200) {
                $output->writeln('Cache purged successfully!');
                $output->writeln(json_encode($content, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            } else {
                $output->writeln('Error purging cache. Status code: ' . $statusCode);
                $output->writeln($response->getContent());
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $output->writeln('An error occurred during the request: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
