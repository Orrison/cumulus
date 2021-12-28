<?php

namespace Cumulus\Cumulus\Commands;

use Exception;
use Cumulus\Cumulus\Helpers;
use Laravel\VaporCli\Helpers as VaporHelpers;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\User;
use Laravel\VaporCli\Commands\Command;

class CloudflareLogoutCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cloudflare:logout')
            ->setDescription('Clear any saved authentication credentials for Cloudflare.');
    }

    /**
     * Execute the command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        Helpers::config(
            [
                'email' => null,
                'apiKey' => null,
            ]
        );

        VaporHelpers::info('Cloudflare credentials cleared.'.PHP_EOL);
    }
}