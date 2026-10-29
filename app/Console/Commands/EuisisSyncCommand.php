<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Euisis\CaseUpdateRecorder;
use App\Services\Euisis\EuisisClient;
use App\Services\Euisis\EuisisException;
use App\Services\Euisis\EuisisSync;
use App\Services\Euisis\StaffLink;
use Illuminate\Console\Command;

/**
 * Pulls EUISIS master data into the court-ms cache and records changes of
 * referred cases for EUISIS to read (docs/euisis-integration.md). Scheduled
 * every five minutes while EUISIS_ENABLED is true. Linked staff accounts are
 * checked after the feed: suspended when the employee left the court's units.
 */
class EuisisSyncCommand extends Command
{
    protected $signature = 'euisis:sync {--bootstrap : Page through the EUISIS directory again before applying the feed}';

    protected $description = 'Sync organizations and people from EUISIS and record referred-case changes';

    public function handle(EuisisClient $client, EuisisSync $sync, CaseUpdateRecorder $updates, StaffLink $staff): int
    {
        $recorded = $updates->capture();
        $this->line("Referred-case changes recorded: {$recorded}");

        if (! $client->enabled()) {
            $this->warn('EUISIS sync is off (EUISIS_ENABLED, EUISIS_URL and EUISIS_TOKEN must all be set).');

            return self::SUCCESS;
        }

        try {
            $summary = $sync->run((bool) $this->option('bootstrap'));
        } catch (EuisisException $e) {
            $this->error($e->getMessage().($e->requestId !== null ? " (request {$e->requestId})" : ''));

            return self::FAILURE;
        }

        if ($summary['bootstrapped']) {
            $this->info("Bootstrapped {$summary['organizations']} organizations and {$summary['units']} units.");
        }
        $this->info($summary['feed']
            ? "Change feed: {$summary['events']} events read, {$summary['applied']} applied."
            : 'The EUISIS change feed is off; the directory is refreshed hourly instead.');

        // Without the feed, the linked employees' records are read again (hourly) before the check.
        $standing = $staff->review(! $summary['feed']);
        $this->info("Staff accounts: {$standing['checked']} checked, {$standing['suspended']} suspended, {$standing['restored']} restored"
            .($standing['errors'] > 0 ? ", {$standing['errors']} not read" : '').'.');

        return self::SUCCESS;
    }
}
