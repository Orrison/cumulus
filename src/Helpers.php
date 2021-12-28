<?php

namespace Cumulus\Cumulus;

use Exception;

class Helpers extends \Laravel\VaporCli\Helpers
{
    /**
     * Get or set configuration values.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return mixed|void
     */
    public static function config($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $value) {
                Config::set($innerKey, $value);
            }

            return;
        }

        return Config::get($key, $value);
    }

    /**
     * Ensure that the user has authenticated with Cloudflare.
     *
     * @return void
     * @throws Exception
     */
    public static function ensureCloudFlareCredentialsAreAvailable()
    {
        if (! static::config('apiToken')) {
            throw new Exception("Please authenticate using the 'cloudflare:login' command before proceeding.");
        }
    }
}