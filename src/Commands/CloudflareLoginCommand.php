<?php

namespace Cumulus\Cumulus\Commands;

use Exception;
use Laravel\VaporCli\Helpers;
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
        $email = Helpers::ask('Email Address');
        $apiKey = Helpers::secret('API Key');

        $key = new APIKey($email, $apiKey);
        $adapter = new Guzzle($key);
        $user = new User($adapter);

        try {
            $userId = $user->getUserID();
        } catch (Exception $e) {
            if ($e->getMessage() === 'Invalid request headers') {
                Helpers::abort(
                    'Invalid credentials'
                );
            }
            throw $e;
        }

        if (gettype($userId) !== 'string') {
            throw new Exception('Something went wrong, please try again');
        }

        \Cumulus\Cumulus\Helpers::config(
            [
                'email' => $email,
                'apiKey' => $apiKey,
            ]
        );

        Helpers::info('Authenticated successfully.'.PHP_EOL);
    }
}