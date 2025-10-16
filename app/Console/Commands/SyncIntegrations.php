<?php

namespace App\Console\Commands;

use App\Models\Integration;
use App\Services\IntegrationService;
use Illuminate\Console\Command;

class SyncIntegrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integrations:sync {--service= : Sync specific service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from external integrations';

    /**
     * Execute the console command.
     */
    public function handle(IntegrationService $integrationService): int
    {
        $service = $this->option('service');

        $query = Integration::where('status', 'connected');
        if ($service) {
            $query->where('service_name', $service);
        }

        $integrations = $query->get();

        if ($integrations->isEmpty()) {
            $this->info('No active integrations found.');
            return 0;
        }

        $this->info("Syncing data from {$integrations->count()} integration(s)...");

        $bar = $this->output->createProgressBar($integrations->count());
        $bar->start();

        foreach ($integrations as $integration) {
            try {
                $this->line("\nSyncing {$integration->service_name}...");
                
                // Sync leads
                $leads = $integrationService->syncLeadsFromCrm($integration);
                $this->line("Synced " . count($leads) . " leads");

                // Sync emails
                $emails = $integrationService->syncEmailsFromService($integration);
                $this->line("Synced " . count($emails) . " emails");

                // Sync calendar events
                $events = $integrationService->syncCalendarEvents($integration);
                $this->line("Synced " . count($events) . " calendar events");

                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Failed to sync {$integration->service_name}: " . $e->getMessage());
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Integration sync completed.");

        return 0;
    }
}
