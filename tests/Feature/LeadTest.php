<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->leadStatus = LeadStatus::factory()->create(['name' => 'Nouveau']);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_leads(): void
    {
        Lead::factory()->count(5)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/leads');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'status',
                        'assigned_to',
                    ],
                ],
            ]);
    }

    public function test_can_create_lead(): void
    {
        $leadData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'company' => 'Test Company',
            'status_id' => $this->leadStatus->id,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/leads', $leadData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'lead' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                ],
            ]);

        $this->assertDatabaseHas('leads', [
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_can_show_lead(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/leads/' . $lead->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'lead' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'status',
                    'tasks',
                    'interactions',
                ],
            ]);
    }

    public function test_can_update_lead(): void
    {
        $lead = Lead::factory()->create();

        $updateData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/v1/leads/' . $lead->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Lead updated successfully',
            ]);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
    }

    public function test_can_delete_lead(): void
    {
        $lead = Lead::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/v1/leads/' . $lead->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Lead deleted successfully',
            ]);

        $this->assertDatabaseMissing('leads', [
            'id' => $lead->id,
        ]);
    }

    public function test_can_assign_lead(): void
    {
        $lead = Lead::factory()->create();
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/leads/' . $lead->id . '/assign', [
            'assigned_to_user_id' => $user->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Lead assigned successfully',
            ]);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'assigned_to_user_id' => $user->id,
        ]);
    }

    public function test_lead_creation_validation(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/leads', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'status_id']);
    }
}
