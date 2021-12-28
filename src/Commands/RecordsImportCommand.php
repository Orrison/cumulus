<?php

namespace Cumulus\Cumulus\Commands;

use Exception;
use Cumulus\Cumulus\Config;
use Cumulus\Cumulus\Helpers;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Adapter\Guzzle;
use Illuminate\Support\Collection;
use Cloudflare\API\Endpoints\Zones;
use Laravel\VaporCli\Helpers as VaporHelpers;
use Laravel\VaporCli\Commands\Command;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('no-proxy', null, InputOption::VALUE_NONE, 'Do not proxy added records')
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

        $vaporRecords = $this->getVaporRecords();

        $key = new APIToken(Config::get('apiToken'));
        $adapter = new Guzzle($key);

        $zone = new Zones($adapter);

        try {
            $cloudflareZoneId = $zone->getZoneId($this->argument('zone'));
        } catch (Exception $e) {
            VaporHelpers::abort('Unable to find a zone with that name / ID in Cloudflare.');
        }

        $dns = new DNS($adapter);

        $cloudflareRecords = collect($dns->listRecords($cloudflareZoneId)->result);

        $proxy = ! $this->option('no-proxy');

        $addedRecords = 0;

        $vaporRecords->each(function ($vaporRecord) use ($cloudflareZoneId, $cloudflareRecords, $dns, $proxy, &$addedRecords) {
            $matches = $cloudflareRecords->filter(function ($cloudflareRecord) use ($vaporRecord) {
                return $cloudflareRecord->name === $vaporRecord['name'] && $cloudflareRecord->type === $vaporRecord['type'];
            });

            if ($matches->isEmpty()) {
                try {
                    $dns->addRecord(
                        $cloudflareZoneId,
                        $vaporRecord['type'],
                        $vaporRecord['name'],
                        $vaporRecord['value'],
                        0,
                        $vaporRecord['type'] !== 'TXT' ? $proxy : false
                    );
                    $addedRecords++;
                    Helpers::info("Added {$vaporRecord['type']} record {$vaporRecord['name']}");
                } catch (Exception $e) {
                    try {
                        $dns->addRecord(
                            $cloudflareZoneId,
                            $vaporRecord['type'],
                            $vaporRecord['name'],
                            $vaporRecord['value'],
                            0,
                            false
                        );
                        $addedRecords++;
                        Helpers::info("Added {$vaporRecord['type']} record {$vaporRecord['name']}");
                    } catch (Exception $e) {
                        VaporHelpers::danger("Unable to create {$vaporRecord['type']} record in Cloudflare with name {$vaporRecord['name']}. Response: {$e->getMessage()}");
                    }
                }
            }
        });

        if ($addedRecords > 0) {
            VaporHelpers::info("Added {$addedRecords} records to Cloudflare.");
        } else {
            VaporHelpers::info("No records were added to Cloudflare. All records are already imported.");
        }
    }

    protected function getVaporRecords(): Collection
    {
        if (! is_numeric($vaporZoneId = $this->argument('zone'))) {
            $vaporZoneId = $this->findIdByName($this->vapor->zones(), $vaporZoneId, 'zone');
        }

        if (is_null($vaporZoneId)) {
            VaporHelpers::abort('Unable to find a zone with that name / ID in Vapor.');
        }

        return collect($this->vapor->records($vaporZoneId))->map(function ($record) {
            return [
                'type' => $record['alias'] ? 'CNAME' : $record['type'],
                'name' => $record['name'],
                'value' => $record['value'],
            ];
        });
    }
}