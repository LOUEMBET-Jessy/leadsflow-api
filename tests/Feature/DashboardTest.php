<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_get_dashboard_summary(): void
    {
        // Create test data
        Lead::factory()->count(10)->create();
        Task::factory()->count(5)->create(['assigned_to_user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/dashboard/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'summary' => [
                    'total_leads',
                    'user_leads',
                    'conversion_rate',
                    'pipeline_value',
                    'monthly_revenue',
                    'recent_leads',
                    'tasks_due_today',
                    'overdue_tasks',
                ],
            ]);
    }

    public function test_can_get_dashboard_charts(): void
    {
        Lead::factory()->count(10)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/dashboard/charts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'charts' => [
                    'leads_by_source',
                    'leads_by_status',
                    'monthly_trend',
                    'conversion_funnel',
                ],
            ]);
    }

    public function test_can_get_recent_leads(): void
    {
        Lead::factory()->count(5)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/dashboard/recent-leads');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'leads' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'status',
                    ],
                ],
            ]);
    }

    public function test_can_get_daily_tasks(): void
    {
        Task::factory()->count(3)->create(['assigned_to_user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/dashboard/daily-tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tasks' => [
                    '*' => [
                        'id',
                        'title',
                        'due_date',
                        'priority',
                        'status',
                    ],
                ],
            ]);
    }

    public function test_can_get_team_performance(): void
    {
        User::factory()->count(3)->create();
        Lead::factory()->count(10)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/dashboard/team-performance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'team_performance' => [
                    '*' => [
                        'user_id',
                        'user_name',
                        'total_leads',
                        'won_leads',
                        'conversion_rate',
                        'completed_tasks',
                    ],
                ],
            ]);
    }
}
