<?php

namespace Cumulus\Cumulus\Commands;

use Exception;
use Cumulus\Cumulus\Helpers;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Adapter\Guzzle;
use Laravel\VaporCli\Commands\Command;
use Laravel\VaporCli\Helpers as VaporHelpers;
use Cumulus\Cumulus\CloudflareEndpoints\UserApiToken;

class CloudflareLoginCommand extends Command
{
    /**
     * Execute the command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $apiToken = VaporHelpers::secret('API Token');

        $key = new APIToken($apiToken);
        $adapter = new Guzzle($key);

        $userApiTokens = new UserApiToken($adapter);

        try {
            $response = $userApiTokens->verifyToken();

            if ($response->status == 'success') {
                throw new Exception('Invalid API Token');
            }
        } catch (Exception $e) {
            if ($e->getMessage() === 'Invalid request headers') {
                VaporHelpers::abort(
                    'Invalid credentials'
                );
            }
            throw $e;
        }

        Helpers::config(
            [
                'apiToken' => $apiToken,
            ]
        );

        VaporHelpers::info('Authenticated successfully.' . PHP_EOL);
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cloudflare:login')
            ->setDescription('Authenticate with Cloudflare');
    }
}