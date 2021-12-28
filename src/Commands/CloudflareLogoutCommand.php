<?php

namespace Cumulus\Cumulus\Commands;

use Exception;
use Cumulus\Cumulus\Helpers;
use Laravel\VaporCli\Commands\Command;
use Laravel\VaporCli\Helpers as VaporHelpers;

class CloudflareLogoutCommand extends Command
{
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
                'apiToken' => null,
            ]
        );

        VaporHelpers::info('Cloudflare credentials cleared.' . PHP_EOL);
    }

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
}