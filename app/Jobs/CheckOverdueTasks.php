<?php

namespace App\Jobs;

use App\Models\Task;
use App\Events\TaskOverdue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckOverdueTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 60;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting overdue tasks check');

            $overdueTasks = Task::where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->where('overdue_notified', false)
                ->get();

            foreach ($overdueTasks as $task) {
                event(new TaskOverdue($task));
                $task->update(['overdue_notified' => true]);
            }

            Log::info('Overdue tasks check completed', [
                'overdue_count' => $overdueTasks->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Overdue tasks check failed', [
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
        Log::error('Overdue tasks check job failed permanently', [
            'error' => $exception->getMessage()
        ]);
    }
}
