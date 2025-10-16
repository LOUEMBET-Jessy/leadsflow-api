<?php

namespace App\Listeners;

use App\Events\LeadCreated;
use App\Services\AutomationService;
use App\Services\AiService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessLeadCreated implements ShouldQueue
{
    use InteractsWithQueue;

    protected AutomationService $automationService;
    protected AiService $aiService;
    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(
        AutomationService $automationService,
        AiService $aiService,
        NotificationService $notificationService
    ) {
        $this->automationService = $automationService;
        $this->aiService = $aiService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(LeadCreated $event): void
    {
        $lead = $event->lead;

        // Process automation triggers
        $this->automationService->processTriggers('lead.created', $lead);

        // Generate AI insights
        $this->aiService->analyzeLead($lead);

        // Calculate initial score
        $score = $this->aiService->scoreLead($lead);
        $lead->update(['score' => $score]);

        // Send notification to assigned user
        if ($lead->assignedTo) {
            $this->notificationService->sendLeadAssignedNotification($lead, $lead->assignedTo);
        }
    }
}
