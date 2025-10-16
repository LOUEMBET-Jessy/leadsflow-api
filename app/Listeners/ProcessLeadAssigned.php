<?php

namespace App\Listeners;

use App\Events\LeadAssigned;
use App\Services\AutomationService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessLeadAssigned implements ShouldQueue
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
    public function handle(LeadAssigned $event): void
    {
        $lead = $event->lead;
        $user = $event->user;

        // Process automation triggers
        $this->automationService->processTriggers('lead.assigned', $lead);

        // Send notification to assigned user
        $this->notificationService->sendLeadAssignedNotification($lead, $user);

        // Create interaction record
        $lead->interactions()->create([
            'user_id' => $user->id,
            'type' => 'note',
            'summary' => 'Lead assigned',
            'details' => 'Lead assigned to ' . $user->name,
            'interaction_date' => now(),
        ]);
    }
}
