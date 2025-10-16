<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiInsight;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiInsightController extends Controller
{
    /**
     * Get AI insights for a specific lead
     */
    public function leadInsights(Lead $lead): JsonResponse
    {
        $insights = $lead->aiInsights()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'lead' => $lead,
            'insights' => $insights
        ]);
    }

    /**
     * Get global AI insights
     */
    public function globalInsights(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $insights = AiInsight::where('user_id', $user->id)
            ->orWhereNull('user_id') // Global insights
            ->with(['lead', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($insights);
    }

    /**
     * Mark insight as read
     */
    public function markAsRead(AiInsight $insight): JsonResponse
    {
        $insight->update(['is_read' => true]);

        return response()->json([
            'message' => 'Insight marked as read',
            'insight' => $insight,
        ]);
    }

    /**
     * Mark all insights as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        
        AiInsight::where('user_id', $user->id)
            ->orWhereNull('user_id')
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'All insights marked as read',
        ]);
    }

    /**
     * Get insight statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $stats = [
            'total' => AiInsight::where('user_id', $user->id)
                ->orWhereNull('user_id')
                ->count(),
            'unread' => AiInsight::where('user_id', $user->id)
                ->orWhereNull('user_id')
                ->where('is_read', false)
                ->count(),
            'read' => AiInsight::where('user_id', $user->id)
                ->orWhereNull('user_id')
                ->where('is_read', true)
                ->count(),
        ];
        
        // Insights by type
        $byType = AiInsight::where('user_id', $user->id)
            ->orWhereNull('user_id')
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');
        
        $stats['by_type'] = $byType;

        return response()->json([
            'statistics' => $stats
        ]);
    }

    /**
     * Generate AI insights for a lead
     */
    public function generateInsights(Lead $lead): JsonResponse
    {
        // This would typically call an AI service
        // For now, we'll create a mock insight
        
        $insight = AiInsight::create([
            'lead_id' => $lead->id,
            'type' => 'recommendation',
            'content' => [
                'title' => 'Lead Analysis Complete',
                'summary' => 'This lead shows high potential based on company size and engagement.',
                'recommendations' => [
                    'Follow up within 24 hours',
                    'Prepare a detailed proposal',
                    'Schedule a demo call'
                ],
                'confidence' => 85
            ],
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'AI insights generated successfully',
            'insight' => $insight,
        ]);
    }

    /**
     * Get all AI insights (index method)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->get('type');
        $limit = $request->get('limit', 15);
        
        $query = AiInsight::where('user_id', $user->id)
            ->orWhereNull('user_id'); // Global insights
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $insights = $query->with(['lead', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'insights' => $insights
        ]);
    }
}
