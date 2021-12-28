<?php

namespace Cumulus\Cumulus\Commands;

use Exception;
use Cumulus\Cumulus\Helpers;
use Laravel\VaporCli\Helpers as VaporHelpers;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\User;
use Laravel\VaporCli\Commands\Command;

class CloudflareLoginCommand extends Command
{
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

    /**
     * Execute the command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $email = VaporHelpers::ask('Email Address');
        $apiKey = VaporHelpers::secret('API Key');

        $key = new APIKey($email, $apiKey);
        $adapter = new Guzzle($key);
        $user = new User($adapter);

        try {
            $userId = $user->getUserID();
        } catch (Exception $e) {
            if ($e->getMessage() === 'Invalid request headers') {
                VaporHelpers::abort(
                    'Invalid credentials'
                );
            }
            throw $e;
        }

        if (gettype($userId) !== 'string') {
            throw new Exception('Something went wrong, please try again');
        }

        Helpers::config(
            [
                'email' => $email,
                'apiKey' => $apiKey,
            ]
        );

        VaporHelpers::info('Authenticated successfully.'.PHP_EOL);
    }
}