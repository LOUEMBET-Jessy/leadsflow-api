<?php

namespace App\Listeners;

use App\Events\TaskOverdue;
use App\Services\AutomationService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessTaskOverdue implements ShouldQueue
{
    use InteractsWithQueue;

    protected AutomationService $automationService;
    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(
        AutomationService $automationService,
        NotificationService $notificationService
    ) {
        $this->automationService = $automationService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(TaskOverdue $event): void
    {
        $task = $event->task;

        // Process automation triggers
        $this->automationService->processTriggers('task.overdue', $task);

        // Send notification to assigned user
        $this->notificationService->sendTaskOverdueNotification($task);
    }
}
