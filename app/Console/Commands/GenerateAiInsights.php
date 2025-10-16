<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Services\AiService;
use Illuminate\Console\Command;

class GenerateAiInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:generate-insights {--lead-id= : Generate insights for a specific lead}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI insights for leads';

    /**
     * Execute the console command.
     */
    public function handle(AiService $aiService): int
    {
        $leadId = $this->option('lead-id');

        if ($leadId) {
            $lead = Lead::find($leadId);
            if (!$lead) {
                $this->error("Lead with ID {$leadId} not found.");
                return 1;
            }

            $this->info("Generating AI insights for lead: {$lead->full_name}");
            $insights = $aiService->analyzeLead($lead);
            $this->info("AI insights generated successfully.");
            $this->line("Quality Score: " . ($insights['quality_score'] ?? 'N/A'));
            $this->line("Conversion Probability: " . ($insights['conversion_probability'] ?? 'N/A') . '%');

            return 0;
        }

        // Generate insights for all leads without recent insights
        $leads = Lead::whereDoesntHave('aiInsights', function ($query) {
            $query->where('created_at', '>=', now()->subDays(7));
        })->limit(10)->get();

        $this->info("Generating AI insights for {$leads->count()} leads...");

        $bar = $this->output->createProgressBar($leads->count());
        $bar->start();

        foreach ($leads as $lead) {
            try {
                $aiService->analyzeLead($lead);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Failed to generate insights for lead {$lead->id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("AI insights generation completed.");

        return 0;
    }
}
