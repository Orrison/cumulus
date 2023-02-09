<?php

namespace Cumulus\Cumulus\Commands;

use Exception;
use Cumulus\Cumulus\Helpers;
use Illuminate\Support\Collection;
use Laravel\VaporCli\Commands\Command;
use Laravel\VaporCli\Helpers as VaporHelpers;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RecordsImportCommand extends Command
{
    protected $dns;
    protected $zone;
    protected $proxy;
    protected $dryRun;
    protected $recordsToAdd;
    protected $totalAddedRecords;
    protected $recordsToUpdate;
    protected $totalUpdatedRecords;
    protected $cloudflareZoneId;
    protected $cloudflareRecords;

    /**
     * Execute the command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->proxy = !$this->option('no-proxy');
        $this->dryRun = $this->option('dry-run');

        VaporHelpers::ensure_api_token_is_available();
        Helpers::ensureCloudFlareCredentialsAreAvailable();

        $vaporRecords = $this->getVaporRecords();

        $this->cloudflareZoneId = Helpers::getCloudflareZoneId($this->argument('zone'));

        $this->dns = Helpers::getCloudflareDnsApi();

        $this->cloudflareRecords = collect($this->dns->listRecords($this->cloudflareZoneId)->result);

        $vaporRecords->each(
            function ($vaporRecord) {
                $matches = $this->cloudflareRecords->filter(function ($cloudflareRecord) use ($vaporRecord) {
                    return $cloudflareRecord->name === $vaporRecord['name'] && $cloudflareRecord->type === $vaporRecord['type'];
                });

                if ($matches->isEmpty()) {
                    // Record does not exist in Cloudflare, add it
                    if ($this->dryRun) {
                        $this->recordsToAdd[] = [
                            'name' => $vaporRecord['name'],
                            'type' => $vaporRecord['type'],
                            'value' => $vaporRecord['value'],
                        ];
                        $this->totalAddedRecords++;
                    } else {
                        $this->addRecord($vaporRecord['type'], $vaporRecord['name'], $vaporRecord['value']);
                    }
                } elseif ($matches->first()->content !== $vaporRecord['value']) {
                    // Record exists in Cloudflare, but the value is different, update it
                    if ($this->dryRun) {
                        $this->recordsToUpdate[] = [
                            'name' => $vaporRecord['name'],
                            'type' => $vaporRecord['type'],
                            'value' => $vaporRecord['value'],
                        ];
                        $this->totalUpdatedRecords++;
                    } else {
                        $this->updateRecord($matches->first(), $vaporRecord['value']);
                    }
                }
            }
        );

        if ($this->dryRun) {
            if ($this->totalAddedRecords > 0) {
                Helpers::info("Records required to be added:");

                $this->table(['Type', 'Name', 'Value'], $this->recordsToAdd);
            } else {
                Helpers::info("No records need added to Cloudflare. All records are already imported.");
            }

            if ($this->totalUpdatedRecords > 0) {
                Helpers::warn("Records required to be updated:");

                $this->table(['Type', 'Name', 'Value'], $this->recordsToUpdate);
            } else {
                Helpers::info("No records need updated in Cloudflare. All records are already correct.");
            }

            return;
        }

        if ($this->totalAddedRecords > 0) {
            Helpers::info("Added {$this->totalAddedRecords} records to Cloudflare.");
        } else {
            Helpers::info("No records were added to Cloudflare. All records are already imported.");
        }

        if ($this->totalUpdatedRecords > 0) {
            Helpers::info("Updated {$this->totalUpdatedRecords} records in Cloudflare.");
        } else {
            Helpers::info("No records need updated in Cloudflare. All records are already correct.");
        }
    }

    protected function getVaporRecords(): Collection
    {
        if (!is_numeric($vaporZoneId = $this->argument('zone'))) {
            $vaporZoneId = $this->findIdByName($this->vapor->zones(), $vaporZoneId, 'zone');
        }

        if (is_null($vaporZoneId)) {
            Helpers::abort('Unable to find a zone with that name / ID in Vapor.');
        }

        return collect($this->vapor->records($vaporZoneId))->map(function ($record) {
            return [
                'type' => $record['alias'] ? 'CNAME' : $record['type'],
                'name' => $record['name'],
                'value' => $record['value'],
            ];
        });
    }

    protected function addRecord($type, $name, $value): void
    {
        try {
            $this->dns->addRecord(
                $this->cloudflareZoneId,
                $type,
                $name,
                $value,
                0,
                $type !== 'TXT' ? $this->proxy : false
            );
            $this->totalAddedRecords++;
            Helpers::info("Added {$type} record {$name}");
        } catch (Exception $e) {
            // If the record fails to be added it could be because proxy is not allowed for this record type, attempt to add it without proxy
            try {
                $this->dns->addRecord(
                    $this->cloudflareZoneId,
                    $type,
                    $name,
                    $value,
                    0,
                    false
                );
                $this->totalAddedRecords++;
                Helpers::info("Added {$type} record {$name}");
            } catch (Exception $e) {
                Helpers::danger(
                    "Unable to create {$type} record in Cloudflare with name {$name}. Response: {$e->getMessage()}"
                );
            }
        }
    }

    protected function updateRecord($record, $value)
    {
        try {
            $this->dns->updateRecordDetails(
                $this->cloudflareZoneId,
                $record->id,
                [
                    'type' => $record->type,
                    'name' => $record->name,
                    'content' => $value,
                    'ttl' => 0
                ]
            );
            $this->totalUpdatedRecords++;
            Helpers::info("Updated {$record->type} record {$record->name}");
        } catch (Exception $e) {
            Helpers::danger(
                "Unable to update record in Cloudflare. Response: {$e->getMessage()}"
            );
        }
    }

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
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'List records to be added without adding them')
            ->setDescription('Import any missing records from Vapor into the Cloudflare zone.');
    }
}