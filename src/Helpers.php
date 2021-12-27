<?php

namespace Cumulus\Cumulus;

use Laravel\VaporCli\Config;

class Helpers extends \Laravel\VaporCli\Helpers
{
    /**
     * Get or set configuration values.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return mixed
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
}