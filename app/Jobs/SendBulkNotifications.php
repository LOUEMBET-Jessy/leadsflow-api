<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $userIds;
    public string $type;
    public array $data;
    public int $tries = 3;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(array $userIds, string $type, array $data)
    {
        $this->userIds = $userIds;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            Log::info('Starting bulk notification send', [
                'user_count' => count($this->userIds),
                'type' => $this->type
            ]);

            $users = User::whereIn('id', $this->userIds)->get();
            
            foreach ($users as $user) {
                $notificationService->sendNotificationByType($user, $this->type, $this->data);
            }

            Log::info('Bulk notification send completed', [
                'user_count' => count($this->userIds),
                'type' => $this->type
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk notification send failed', [
                'user_count' => count($this->userIds),
                'type' => $this->type,
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
        Log::error('Bulk notification job failed permanently', [
            'user_count' => count($this->userIds),
            'type' => $this->type,
            'error' => $exception->getMessage()
        ]);
    }
}
