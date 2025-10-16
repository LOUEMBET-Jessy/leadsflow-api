<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\AiInsight;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.ai.api_key');
        $this->baseUrl = config('services.ai.base_url', 'https://api.openai.com/v1');
    }

    /**
     * Analyze lead and generate insights
     */
    public function analyzeLead(Lead $lead): array
    {
        try {
            $context = $this->buildLeadContext($lead);
            $prompt = $this->buildAnalysisPrompt($context);
            
            $response = $this->callAiApi($prompt);
            
            if ($response['success']) {
                $this->saveInsight($lead, $response['data']);
                return $response['data'];
            }
            
            return $this->getDefaultInsight($lead);
        } catch (\Exception $e) {
            Log::error('AI Analysis failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->getDefaultInsight($lead);
        }
    }

    /**
     * Generate global insights
     */
    public function generateGlobalInsights(User $user = null): array
    {
        try {
            $context = $this->buildGlobalContext($user);
            $prompt = $this->buildGlobalInsightPrompt($context);
            
            $response = $this->callAiApi($prompt);
            
            if ($response['success']) {
                $this->saveGlobalInsight($user, $response['data']);
                return $response['data'];
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Global AI Insights failed', [
                'user_id' => $user?->id,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Score lead based on various factors
     */
    public function scoreLead(Lead $lead): int
    {
        $score = 0;
        
        // Base score
        $score += 10;
        
        // Company size bonus
        if ($lead->company_size) {
            $score += match ($lead->company_size) {
                'Large' => 30,
                'Medium' => 20,
                'Small' => 10,
                default => 5,
            };
        }
        
        // Priority bonus
        $score += match ($lead->priority) {
            'Hot' => 40,
            'Warm' => 20,
            'Cold' => 5,
            default => 10,
        };
        
        // Industry bonus (high-value industries)
        if ($lead->industry) {
            $highValueIndustries = ['Technology', 'Finance', 'Healthcare', 'Manufacturing'];
            if (in_array($lead->industry, $highValueIndustries)) {
                $score += 15;
            }
        }
        
        // Recent interaction bonus
        if ($lead->last_contact_date && $lead->last_contact_date->diffInDays(now()) <= 7) {
            $score += 10;
        }
        
        // Email domain bonus (corporate domains)
        if ($lead->email) {
            $domain = substr(strrchr($lead->email, "@"), 1);
            $corporateDomains = ['gmail.com', 'yahoo.com', 'hotmail.com'];
            if (!in_array($domain, $corporateDomains)) {
                $score += 5;
            }
        }
        
        // Notes analysis (simple keyword matching)
        if ($lead->notes) {
            $positiveKeywords = ['urgent', 'budget', 'decision', 'interested', 'ready'];
            $negativeKeywords = ['not interested', 'no budget', 'later'];
            
            $notesLower = strtolower($lead->notes);
            
            foreach ($positiveKeywords as $keyword) {
                if (strpos($notesLower, $keyword) !== false) {
                    $score += 5;
                }
            }
            
            foreach ($negativeKeywords as $keyword) {
                if (strpos($notesLower, $keyword) !== false) {
                    $score -= 10;
                }
            }
        }
        
        return min(max($score, 0), 100); // Ensure score is between 0 and 100
    }

    /**
     * Build context for lead analysis
     */
    protected function buildLeadContext(Lead $lead): array
    {
        return [
            'lead' => [
                'name' => $lead->full_name,
                'email' => $lead->email,
                'company' => $lead->company,
                'title' => $lead->title,
                'industry' => $lead->industry,
                'company_size' => $lead->company_size,
                'priority' => $lead->priority,
                'source' => $lead->source,
                'notes' => $lead->notes,
                'last_contact' => $lead->last_contact_date?->format('Y-m-d H:i:s'),
            ],
            'interactions' => $lead->interactions()->latest()->limit(5)->get()->map(function ($interaction) {
                return [
                    'type' => $interaction->type,
                    'summary' => $interaction->summary,
                    'date' => $interaction->interaction_date->format('Y-m-d H:i:s'),
                ];
            })->toArray(),
        ];
    }

    /**
     * Build context for global insights
     */
    protected function buildGlobalContext(User $user = null): array
    {
        $query = Lead::query();
        
        if ($user) {
            $query->where('assigned_to_user_id', $user->id);
        }
        
        $leads = $query->with(['status', 'interactions'])->get();
        
        return [
            'total_leads' => $leads->count(),
            'leads_by_status' => $leads->groupBy('status.name')->map->count(),
            'leads_by_priority' => $leads->groupBy('priority')->map->count(),
            'leads_by_source' => $leads->groupBy('source')->map->count(),
            'recent_interactions' => $leads->flatMap->interactions->count(),
            'conversion_rate' => $this->calculateConversionRate($leads),
        ];
    }

    /**
     * Build analysis prompt for AI
     */
    protected function buildAnalysisPrompt(array $context): string
    {
        return "Analyze this lead and provide insights:\n\n" . 
               "Lead Information:\n" . json_encode($context['lead'], JSON_PRETTY_PRINT) . "\n\n" .
               "Recent Interactions:\n" . json_encode($context['interactions'], JSON_PRETTY_PRINT) . "\n\n" .
               "Please provide:\n" .
               "1. Lead quality assessment (1-10)\n" .
               "2. Recommended next actions\n" .
               "3. Potential concerns or red flags\n" .
               "4. Estimated probability of conversion\n" .
               "5. Suggested communication approach";
    }

    /**
     * Build global insight prompt for AI
     */
    protected function buildGlobalInsightPrompt(array $context): string
    {
        return "Analyze this lead data and provide strategic insights:\n\n" . 
               json_encode($context, JSON_PRETTY_PRINT) . "\n\n" .
               "Please provide:\n" .
               "1. Performance trends\n" .
               "2. Areas for improvement\n" .
               "3. Recommended strategies\n" .
               "4. Risk factors\n" .
               "5. Opportunities";
    }

    /**
     * Call AI API
     */
    protected function callAiApi(string $prompt): array
    {
        // For now, return mock data. In production, integrate with OpenAI or similar
        return [
            'success' => true,
            'data' => [
                'quality_score' => rand(6, 10),
                'recommendations' => [
                    'Follow up within 24 hours',
                    'Prepare a detailed proposal',
                    'Schedule a demo call'
                ],
                'concerns' => [],
                'conversion_probability' => rand(60, 90),
                'communication_approach' => 'Professional and consultative'
            ]
        ];
    }

    /**
     * Save insight to database
     */
    protected function saveInsight(Lead $lead, array $data): void
    {
        AiInsight::create([
            'lead_id' => $lead->id,
            'type' => 'recommendation',
            'content' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Save global insight to database
     */
    protected function saveGlobalInsight(User $user = null, array $data): void
    {
        AiInsight::create([
            'user_id' => $user?->id,
            'type' => 'summary',
            'content' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Get default insight when AI fails
     */
    protected function getDefaultInsight(Lead $lead): array
    {
        return [
            'quality_score' => 5,
            'recommendations' => ['Follow up as scheduled'],
            'concerns' => [],
            'conversion_probability' => 50,
            'communication_approach' => 'Standard follow-up'
        ];
    }

    /**
     * Calculate conversion rate
     */
    protected function calculateConversionRate($leads): float
    {
        $total = $leads->count();
        if ($total === 0) return 0;
        
        $won = $leads->where('status.name', 'Gagné')->count();
        return round(($won / $total) * 100, 2);
    }
}
