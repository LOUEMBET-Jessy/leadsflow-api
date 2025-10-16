<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// Lead Events
use App\Events\LeadCreated;
use App\Events\LeadStatusChanged;
use App\Events\LeadAssigned;
use App\Events\TaskDue;
use App\Events\TaskOverdue;

// Lead Listeners
use App\Listeners\ProcessLeadCreated;
use App\Listeners\ProcessLeadStatusChanged;
use App\Listeners\ProcessLeadAssigned;
use App\Listeners\ProcessTaskDue;
use App\Listeners\ProcessTaskOverdue;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Lead Events
        LeadCreated::class => [
            ProcessLeadCreated::class,
        ],

        LeadStatusChanged::class => [
            ProcessLeadStatusChanged::class,
        ],

        LeadAssigned::class => [
            ProcessLeadAssigned::class,
        ],

        TaskDue::class => [
            ProcessTaskDue::class,
        ],

        TaskOverdue::class => [
            ProcessTaskOverdue::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
