<?php

namespace Cumulus\Cumulus;

use Exception;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\Zones;
use Laravel\VaporCli\Helpers as VaporHelpers;

class Helpers extends VaporHelpers
{
    /**
     * Ensure that the user has authenticated with Cloudflare.
     *
     * @return void
     * @throws Exception
     */
    public static function ensureCloudFlareCredentialsAreAvailable()
    {
        if (!static::config('apiToken')) {
            throw new Exception("Please authenticate using the 'cloudflare:login' command before proceeding.");
        }
    }

    /**
     * Get or set configuration values.
     *
     * @param string|array $key
     * @param mixed $value
     *
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
     * @param string $zoneInput
     *
     * @return string|void
     */
    public static function getCloudflareZoneId($zoneInput)
    {
        $key = new APIToken(Config::get('apiToken'));
        $adapter = new Guzzle($key);

        $zone = new Zones($adapter);

        try {
            return $zone->getZoneId($zoneInput);
        } catch (Exception $e) {
            parent::abort('Unable to find a zone with that name / ID in Cloudflare.');
        }
    }

    /**
     * @return DNS
     */
    public static function getCloudflareDnsApi()
    {
        $key = new APIToken(Config::get('apiToken'));
        $adapter = new Guzzle($key);

        return new DNS($adapter);
    }
}