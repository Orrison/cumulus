<?php

namespace Cumulus\Cumulus\Commands;

use Exception;
use Illuminate\Support\Str;
use Cumulus\Cumulus\Config;
use Cumulus\Cumulus\Helpers;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\Zones;
use Laravel\VaporCli\Helpers as VaporHelpers;
use Laravel\VaporCli\Commands\Command;
use Symfony\Component\Console\Input\InputArgument;

class RecordsImportCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('records:import')
            ->addArgument('zone', InputArgument::REQUIRED, 'The zone name / ID')
            ->setDescription('Import any missing records from Vapor into the Cloudflare zone.');
    }

    /**
     * Execute the command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        VaporHelpers::ensure_api_token_is_available();
        Helpers::ensureCloudFlareCredentialsAreAvailable();

        if (! is_numeric($zoneId = $this->argument('zone'))) {
            $zoneId = $this->findIdByName($this->vapor->zones(), $zoneId, 'zone');
        }

        if (is_null($zoneId)) {
            VaporHelpers::abort('Unable to find a zone with that name / ID in Vapor.');
        }

        $records = collect($this->vapor->records($zoneId))->map(function ($record) {
            return [
                'type' => $record['alias'] ? 'CNAME' : $record['type'],
                'name' => $record['name'],
                'value' => $record['value'],
            ];
        });

        $key = new APIKey(Config::get('email'), Config::get('apiKey'));
        $adapter = new Guzzle($key);
        $zone = new Zones($adapter);
        $dns = new DNS($adapter);

        try {
            $zoneId = $zone->getZoneId($this->argument('zone'));
        } catch (Exception $e) {
            VaporHelpers::abort('Unable to find a zone with that name / ID in Cloudflare.');
        }

        $cloudflareRecords = $dns->listRecords($zoneId);

        var_dump($cloudflareRecords);
    }
}