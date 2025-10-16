<?php

namespace Tests\Unit;

use App\Models\Lead;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AiService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = new AiService();
    }

    public function test_can_score_lead(): void
    {
        $lead = Lead::factory()->create([
            'priority' => 'Hot',
            'company_size' => 'Large',
            'industry' => 'Technology',
            'notes' => 'urgent budget decision',
        ]);

        $score = $this->aiService->scoreLead($lead);

        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_score_includes_priority_bonus(): void
    {
        $hotLead = Lead::factory()->create(['priority' => 'Hot']);
        $coldLead = Lead::factory()->create(['priority' => 'Cold']);

        $hotScore = $this->aiService->scoreLead($hotLead);
        $coldScore = $this->aiService->scoreLead($coldLead);

        $this->assertGreaterThan($coldScore, $hotScore);
    }

    public function test_score_includes_company_size_bonus(): void
    {
        $largeLead = Lead::factory()->create(['company_size' => 'Large']);
        $smallLead = Lead::factory()->create(['company_size' => 'Small']);

        $largeScore = $this->aiService->scoreLead($largeLead);
        $smallScore = $this->aiService->scoreLead($smallLead);

        $this->assertGreaterThan($smallScore, $largeScore);
    }

    public function test_score_includes_industry_bonus(): void
    {
        $techLead = Lead::factory()->create(['industry' => 'Technology']);
        $otherLead = Lead::factory()->create(['industry' => 'Other']);

        $techScore = $this->aiService->scoreLead($techLead);
        $otherScore = $this->aiService->scoreLead($otherLead);

        $this->assertGreaterThan($otherScore, $techScore);
    }

    public function test_score_includes_notes_analysis(): void
    {
        $positiveLead = Lead::factory()->create(['notes' => 'urgent budget decision interested']);
        $negativeLead = Lead::factory()->create(['notes' => 'not interested no budget']);

        $positiveScore = $this->aiService->scoreLead($positiveLead);
        $negativeScore = $this->aiService->scoreLead($negativeLead);

        $this->assertGreaterThan($negativeScore, $positiveScore);
    }

    public function test_score_is_within_bounds(): void
    {
        $lead = Lead::factory()->create();

        $score = $this->aiService->scoreLead($lead);

        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }
}
