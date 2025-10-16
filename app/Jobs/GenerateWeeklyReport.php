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
use Carbon\Carbon;

class GenerateWeeklyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $userId;
    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            Log::info('Starting weekly report generation', ['user_id' => $this->userId]);

            $users = $this->userId ? User::where('id', $this->userId)->get() : User::all();
            
            foreach ($users as $user) {
                $summary = $this->generateUserSummary($user);
                $notificationService->sendWeeklySummaryNotification($user, $summary);
            }

            Log::info('Weekly report generation completed', ['user_count' => $users->count()]);
        } catch (\Exception $e) {
            Log::error('Weekly report generation failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate summary for user
     */
    protected function generateUserSummary(User $user): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $leads = $user->assignedLeads()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        $tasks = $user->assignedTasks()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        $completedTasks = $tasks->where('status', 'completed');
        $overdueTasks = $user->assignedTasks()
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->get();

        return [
            'period' => $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d, Y'),
            'leads' => [
                'total' => $leads->count(),
                'new' => $leads->where('created_at', '>=', $startOfWeek)->count(),
                'converted' => $leads->where('status.name', 'Gagné')->count(),
            ],
            'tasks' => [
                'total' => $tasks->count(),
                'completed' => $completedTasks->count(),
                'overdue' => $overdueTasks->count(),
                'completion_rate' => $tasks->count() > 0 ? round(($completedTasks->count() / $tasks->count()) * 100, 2) : 0,
            ],
            'performance' => [
                'conversion_rate' => $leads->count() > 0 ? round(($leads->where('status.name', 'Gagné')->count() / $leads->count()) * 100, 2) : 0,
                'avg_score' => $leads->avg('score') ?? 0,
            ],
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Weekly report job failed permanently', [
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);
    }
}
