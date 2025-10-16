<?php

namespace App\Jobs;

use App\Models\Integration;
use App\Services\IntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncExternalData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Integration $integration;
    public string $dataType;
    public int $tries = 3;
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(Integration $integration, string $dataType = 'all')
    {
        $this->integration = $integration;
        $this->dataType = $dataType;
    }

    /**
     * Execute the job.
     */
    public function handle(IntegrationService $integrationService): void
    {
        try {
            Log::info('Starting external data sync', [
                'integration_id' => $this->integration->id,
                'service' => $this->integration->service_name,
                'data_type' => $this->dataType
            ]);

            $results = [];

            switch ($this->dataType) {
                case 'leads':
                    $results = $integrationService->syncLeadsFromCrm($this->integration);
                    break;
                case 'emails':
                    $results = $integrationService->syncEmailsFromService($this->integration);
                    break;
                case 'calendar':
                    $results = $integrationService->syncCalendarEvents($this->integration);
                    break;
                case 'all':
                default:
                    $results['leads'] = $integrationService->syncLeadsFromCrm($this->integration);
                    $results['emails'] = $integrationService->syncEmailsFromService($this->integration);
                    $results['calendar'] = $integrationService->syncCalendarEvents($this->integration);
                    break;
            }

            Log::info('External data sync completed', [
                'integration_id' => $this->integration->id,
                'service' => $this->integration->service_name,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('External data sync failed', [
                'integration_id' => $this->integration->id,
                'service' => $this->integration->service_name,
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
        Log::error('External data sync job failed permanently', [
            'integration_id' => $this->integration->id,
            'service' => $this->integration->service_name,
            'error' => $exception->getMessage()
        ]);
    }
}
