<?php

namespace App\Events;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Lead $lead;
    public User $user;
    public ?User $previousUser;

    /**
     * Create a new event instance.
     */
    public function __construct(Lead $lead, User $user, ?User $previousUser = null)
    {
        $this->lead = $lead;
        $this->user = $user;
        $this->previousUser = $previousUser;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('leads'),
        ];
    }
}
