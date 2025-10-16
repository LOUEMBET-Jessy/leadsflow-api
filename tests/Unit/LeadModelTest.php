<?php

namespace Tests\Unit;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_has_full_name_attribute(): void
    {
        $lead = Lead::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $lead->full_name);
    }

    public function test_lead_belongs_to_assigned_user(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create(['assigned_to_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $lead->assignedTo);
        $this->assertEquals($user->id, $lead->assignedTo->id);
    }

    public function test_lead_belongs_to_created_by_user(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create(['created_by_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $lead->createdBy);
        $this->assertEquals($user->id, $lead->createdBy->id);
    }

    public function test_lead_belongs_to_status(): void
    {
        $status = LeadStatus::factory()->create();
        $lead = Lead::factory()->create(['status_id' => $status->id]);

        $this->assertInstanceOf(LeadStatus::class, $lead->status);
        $this->assertEquals($status->id, $lead->status->id);
    }

    public function test_lead_has_many_tasks(): void
    {
        $lead = Lead::factory()->create();
        $tasks = \App\Models\Task::factory()->count(3)->create(['lead_id' => $lead->id]);

        $this->assertCount(3, $lead->tasks);
        $this->assertInstanceOf(\App\Models\Task::class, $lead->tasks->first());
    }

    public function test_lead_has_many_interactions(): void
    {
        $lead = Lead::factory()->create();
        $interactions = \App\Models\Interaction::factory()->count(2)->create(['lead_id' => $lead->id]);

        $this->assertCount(2, $lead->interactions);
        $this->assertInstanceOf(\App\Models\Interaction::class, $lead->interactions->first());
    }

    public function test_lead_scope_by_status(): void
    {
        $status = LeadStatus::factory()->create();
        Lead::factory()->create(['status_id' => $status->id]);
        Lead::factory()->create(['status_id' => LeadStatus::factory()->create()->id]);

        $leads = Lead::byStatus($status->id)->get();

        $this->assertCount(1, $leads);
        $this->assertEquals($status->id, $leads->first()->status_id);
    }

    public function test_lead_scope_by_priority(): void
    {
        Lead::factory()->create(['priority' => 'Hot']);
        Lead::factory()->create(['priority' => 'Warm']);

        $hotLeads = Lead::byPriority('Hot')->get();

        $this->assertCount(1, $hotLeads);
        $this->assertEquals('Hot', $hotLeads->first()->priority);
    }

    public function test_lead_scope_by_assigned_user(): void
    {
        $user = User::factory()->create();
        Lead::factory()->create(['assigned_to_user_id' => $user->id]);
        Lead::factory()->create(['assigned_to_user_id' => User::factory()->create()->id]);

        $userLeads = Lead::byAssignedUser($user->id)->get();

        $this->assertCount(1, $userLeads);
        $this->assertEquals($user->id, $userLeads->first()->assigned_to_user_id);
    }
}
