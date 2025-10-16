<?php

namespace App\Listeners;

use App\Events\LeadStatusChanged;
use App\Services\AutomationService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessLeadStatusChanged implements ShouldQueue
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
    public function handle(LeadStatusChanged $event): void
    {
        $lead = $event->lead;

        // Process automation triggers
        $this->automationService->processTriggers('lead.status_changed', $lead);

        // Send notification to assigned user
        if ($lead->assignedTo) {
            $this->notificationService->sendLeadStatusChangedNotification(
                $lead,
                $lead->assignedTo,
                $event->oldStatus,
                $event->newStatus
            );
        }

        // Update last contact date if status indicates contact
        if (in_array($event->newStatus, ['Contacté', 'Qualifié', 'Négociation'])) {
            $lead->update(['last_contact_date' => now()]);
        }
    }
}
