<?php

namespace App\Console\Commands;

use App\Models\Automation;
use App\Services\AutomationService;
use Illuminate\Console\Command;

class ProcessAutomations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automations:process {--automation-id= : Process specific automation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automation rules';

    /**
     * Execute the console command.
     */
    public function handle(AutomationService $automationService): int
    {
        $automationId = $this->option('automation-id');

        if ($automationId) {
            $automation = Automation::find($automationId);
            if (!$automation) {
                $this->error("Automation with ID {$automationId} not found.");
                return 1;
            }

            $this->info("Processing automation: {$automation->name}");
            // Process specific automation logic here
            $this->info("Automation processed successfully.");

            return 0;
        }

        // Process all active automations
        $automations = Automation::where('is_active', true)->get();

        if ($automations->isEmpty()) {
            $this->info('No active automations found.');
            return 0;
        }

        $this->info("Processing {$automations->count()} automation(s)...");

        $bar = $this->output->createProgressBar($automations->count());
        $bar->start();

        foreach ($automations as $automation) {
            try {
                // Process automation logic here
                $this->line("\nProcessing: {$automation->name}");
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Failed to process automation {$automation->id}: " . $e->getMessage());
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Automation processing completed.");

        return 0;
    }
}
