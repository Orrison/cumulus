<?php

namespace Cumulus\Cumulus;

use Laravel\VaporCli\Helpers;

class Config extends \Laravel\VaporCli\Config
{
    /**
     * Get the path to the configuration file.
     *
     * @return string
     */
    protected static function path()
    {
        return Helpers::home() . '/.cumulus/config.json';
    }
}