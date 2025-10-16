<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProcessLeadImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $leadsData;
    public int $userId;
    public int $tries = 3;
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(array $leadsData, int $userId)
    {
        $this->leadsData = $leadsData;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting lead import', [
                'count' => count($this->leadsData),
                'user_id' => $this->userId
            ]);

            $imported = 0;
            $errors = [];

            $defaultStatus = LeadStatus::where('name', 'Nouveau')->first();
            $defaultStage = PipelineStage::whereHas('pipeline', function ($query) {
                $query->where('is_default', true);
            })->orderBy('order')->first();

            foreach ($this->leadsData as $index => $leadData) {
                try {
                    $validator = Validator::make($leadData, [
                        'first_name' => 'required|string|max:255',
                        'last_name' => 'required|string|max:255',
                        'email' => 'required|email|max:255',
                        'phone' => 'nullable|string|max:20',
                        'company' => 'nullable|string|max:255',
                        'title' => 'nullable|string|max:255',
                        'source' => 'nullable|string|max:255',
                    ]);

                    if ($validator->fails()) {
                        $errors[] = [
                            'row' => $index + 1,
                            'errors' => $validator->errors()->toArray()
                        ];
                        continue;
                    }

                    Lead::create([
                        'first_name' => $leadData['first_name'],
                        'last_name' => $leadData['last_name'],
                        'email' => $leadData['email'],
                        'phone' => $leadData['phone'] ?? null,
                        'company' => $leadData['company'] ?? null,
                        'title' => $leadData['title'] ?? null,
                        'source' => $leadData['source'] ?? 'Import',
                        'status_id' => $defaultStatus->id,
                        'pipeline_stage_id' => $defaultStage->id ?? null,
                        'created_by_user_id' => $this->userId,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'error' => $e->getMessage()
                    ];
                }
            }

            Log::info('Lead import completed', [
                'imported' => $imported,
                'errors' => count($errors),
                'user_id' => $this->userId
            ]);
        } catch (\Exception $e) {
            Log::error('Lead import failed', [
                'user_id' => $this->userId,
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
        Log::error('Lead import job failed permanently', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);
    }
}
