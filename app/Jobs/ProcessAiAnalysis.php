<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Lead $lead;
    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Execute the job.
     */
    public function handle(AiService $aiService): void
    {
        try {
            Log::info('Starting AI analysis for lead', ['lead_id' => $this->lead->id]);
            
            $insights = $aiService->analyzeLead($this->lead);
            
            Log::info('AI analysis completed', [
                'lead_id' => $this->lead->id,
                'insights' => $insights
            ]);
        } catch (\Exception $e) {
            Log::error('AI analysis failed', [
                'lead_id' => $this->lead->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AI analysis job failed permanently', [
            'lead_id' => $this->lead->id,
            'error' => $exception->getMessage()
        ]);
    }
}
