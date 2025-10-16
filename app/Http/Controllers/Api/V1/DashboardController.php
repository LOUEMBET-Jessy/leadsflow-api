<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Interaction;
use App\Models\AiInsight;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear = $now->copy()->startOfYear();

        // Total leads
        $totalLeads = Lead::count();
        $userLeads = Lead::where('assigned_to_user_id', $user->id)->count();

        // Conversion rate (leads won / total leads)
        $wonLeads = Lead::whereHas('status', function ($query) {
            $query->where('name', 'Gagné');
        })->count();
        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 2) : 0;

        // Pipeline value (sum of estimated values)
        $pipelineValue = Lead::whereHas('status', function ($query) {
            $query->where('is_final', false);
        })->sum('score'); // Using score as estimated value for now

        // Monthly revenue (leads won this month)
        $monthlyRevenue = Lead::whereHas('status', function ($query) {
            $query->where('name', 'Gagné');
        })->where('updated_at', '>=', $startOfMonth)->sum('score');

        // Recent leads (last 7 days)
        $recentLeads = Lead::where('created_at', '>=', $now->copy()->subDays(7))->count();

        // Tasks due today
        $tasksDueToday = Task::where('assigned_to_user_id', $user->id)
            ->whereDate('due_date', $now->toDateString())
            ->where('status', '!=', 'completed')
            ->count();

        // Overdue tasks
        $overdueTasks = Task::where('assigned_to_user_id', $user->id)
            ->where('due_date', '<', $now)
            ->where('status', '!=', 'completed')
            ->count();

        return response()->json([
            'summary' => [
                'total_leads' => $totalLeads,
                'user_leads' => $userLeads,
                'conversion_rate' => $conversionRate,
                'pipeline_value' => $pipelineValue,
                'monthly_revenue' => $monthlyRevenue,
                'recent_leads' => $recentLeads,
                'tasks_due_today' => $tasksDueToday,
                'overdue_tasks' => $overdueTasks,
            ]
        ]);
    }

    /**
     * Get dashboard charts data
     */
    public function charts(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // month, quarter, year
        $now = Carbon::now();

        $startDate = match ($period) {
            'week' => $now->copy()->subWeek(),
            'month' => $now->copy()->subMonth(),
            'quarter' => $now->copy()->subQuarter(),
            'year' => $now->copy()->subYear(),
            default => $now->copy()->subMonth(),
        };

        // Leads by source
        $leadsBySource = Lead::where('created_at', '>=', $startDate)
            ->select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->get();

        // Leads by status
        $leadsByStatus = Lead::where('created_at', '>=', $startDate)
            ->join('lead_statuses', 'leads.status_id', '=', 'lead_statuses.id')
            ->select('lead_statuses.name as status', DB::raw('count(*) as count'))
            ->groupBy('lead_statuses.name')
            ->get();

        // Monthly leads trend
        $monthlyTrend = Lead::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Conversion funnel
        $conversionFunnel = Lead::where('created_at', '>=', $startDate)
            ->join('lead_statuses', 'leads.status_id', '=', 'lead_statuses.id')
            ->select('lead_statuses.name as stage', DB::raw('count(*) as count'))
            ->groupBy('lead_statuses.name')
            ->orderBy('lead_statuses.order')
            ->get();

        return response()->json([
            'charts' => [
                'leads_by_source' => $leadsBySource,
                'leads_by_status' => $leadsByStatus,
                'monthly_trend' => $monthlyTrend,
                'conversion_funnel' => $conversionFunnel,
            ]
        ]);
    }

    /**
     * Get recent leads
     */
    public function recentLeads(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $leads = Lead::with(['status', 'assignedTo', 'pipelineStage'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'leads' => $leads
        ]);
    }

    /**
     * Get daily tasks
     */
    public function dailyTasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = Carbon::now();

        $tasks = Task::where('assigned_to_user_id', $user->id)
            ->where(function ($query) use ($now) {
                $query->whereDate('due_date', $now->toDateString())
                      ->orWhere('due_date', '<', $now);
            })
            ->where('status', '!=', 'completed')
            ->with(['lead', 'createdBy'])
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    /**
     * Get team performance
     */
    public function teamPerformance(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $now = Carbon::now();

        $startDate = match ($period) {
            'week' => $now->copy()->subWeek(),
            'month' => $now->copy()->subMonth(),
            'quarter' => $now->copy()->subQuarter(),
            'year' => $now->copy()->subYear(),
            default => $now->copy()->subMonth(),
        };

        $teamPerformance = User::withCount([
            'assignedLeads as total_leads',
            'assignedLeads as won_leads' => function ($query) {
                $query->whereHas('status', function ($q) {
                    $q->where('name', 'Gagné');
                });
            },
            'assignedTasks as completed_tasks' => function ($query) {
                $query->where('status', 'completed');
            }
        ])
        ->whereHas('assignedLeads', function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        })
        ->get()
        ->map(function ($user) {
            $conversionRate = $user->total_leads > 0 
                ? round(($user->won_leads / $user->total_leads) * 100, 2) 
                : 0;

            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_leads' => $user->total_leads,
                'won_leads' => $user->won_leads,
                'conversion_rate' => $conversionRate,
                'completed_tasks' => $user->completed_tasks,
            ];
        });

        return response()->json([
            'team_performance' => $teamPerformance
        ]);
    }

    /**
     * Get pipeline overview
     */
    public function pipelineOverview(Request $request): JsonResponse
    {
        $pipelineId = $request->get('pipeline_id');
        
        $query = Lead::with(['pipelineStage', 'status']);
        
        if ($pipelineId) {
            $query->whereHas('pipelineStage', function ($q) use ($pipelineId) {
                $q->where('pipeline_id', $pipelineId);
            });
        }

        $pipelineOverview = $query->get()
            ->groupBy('pipelineStage.name')
            ->map(function ($leads, $stageName) {
                return [
                    'stage' => $stageName,
                    'count' => $leads->count(),
                    'leads' => $leads->take(5) // Show first 5 leads for preview
                ];
            });

        return response()->json([
            'pipeline_overview' => $pipelineOverview
        ]);
    }

    /**
     * Get AI recommendations
     */
    public function aiRecommendations(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $recommendations = AiInsight::where('user_id', $user->id)
            ->orWhereNull('user_id') // Global recommendations
            ->where('type', 'recommendation')
            ->where('is_read', false)
            ->with(['lead'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'recommendations' => $recommendations
        ]);
    }

    /**
     * Get dashboard stats (alias for summary)
     */
    public function stats(Request $request): JsonResponse
    {
        return $this->summary($request);
    }

    /**
     * Get dashboard activity
     */
    public function activity(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->get('limit', 20);
        
        // Get recent activities from leads, tasks, and interactions
        $activities = collect();
        
        // Recent leads
        $recentLeads = Lead::with(['status', 'assignedTo'])
            ->where('assigned_to_user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->limit($limit / 3)
            ->get()
            ->map(function ($lead) {
                return [
                    'type' => 'lead',
                    'action' => 'updated',
                    'description' => "Lead {$lead->name} mis à jour",
                    'timestamp' => $lead->updated_at,
                    'data' => $lead
                ];
            });
        
        // Recent tasks
        $recentTasks = Task::with(['lead', 'assignedTo'])
            ->where('assigned_to_user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->limit($limit / 3)
            ->get()
            ->map(function ($task) {
                return [
                    'type' => 'task',
                    'action' => 'updated',
                    'description' => "Tâche {$task->title} mise à jour",
                    'timestamp' => $task->updated_at,
                    'data' => $task
                ];
            });
        
        // Recent interactions
        $recentInteractions = Interaction::with(['lead', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit / 3)
            ->get()
            ->map(function ($interaction) {
                return [
                    'type' => 'interaction',
                    'action' => 'created',
                    'description' => "Interaction {$interaction->type} ajoutée",
                    'timestamp' => $interaction->created_at,
                    'data' => $interaction
                ];
            });
        
        $activities = $activities
            ->merge($recentLeads)
            ->merge($recentTasks)
            ->merge($recentInteractions)
            ->sortByDesc('timestamp')
            ->take($limit);

        return response()->json([
            'activities' => $activities->values()
        ]);
    }

    /**
     * Get conversion funnel data
     */
    public function funnel(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $now = Carbon::now();

        $startDate = match ($period) {
            'week' => $now->copy()->subWeek(),
            'month' => $now->copy()->subMonth(),
            'quarter' => $now->copy()->subQuarter(),
            'year' => $now->copy()->subYear(),
            default => $now->copy()->subMonth(),
        };

        try {
            // Get leads by pipeline stage
            $funnelData = Lead::where('created_at', '>=', $startDate)
                ->join('pipeline_stages', 'leads.pipeline_stage_id', '=', 'pipeline_stages.id')
                ->select(
                    'pipeline_stages.name as stage',
                    'pipeline_stages.order as stage_order',
                    DB::raw('count(*) as count')
                )
                ->groupBy('pipeline_stages.name', 'pipeline_stages.order')
                ->orderBy('pipeline_stages.order')
                ->get();

            // Calculate conversion rates
            $totalLeads = $funnelData->sum('count');
            $funnelData = $funnelData->map(function ($stage, $index) use ($totalLeads) {
                $conversionRate = $totalLeads > 0 ? round(($stage->count / $totalLeads) * 100, 2) : 0;
                return [
                    'stage' => $stage->stage,
                    'order' => $stage->stage_order,
                    'count' => $stage->count,
                    'conversion_rate' => $conversionRate
                ];
            });

            return response()->json([
                'funnel' => $funnelData,
                'total_leads' => $totalLeads,
                'period' => $period
            ]);
        } catch (\Exception $e) {
            // Fallback: return empty funnel data
            return response()->json([
                'funnel' => [],
                'total_leads' => 0,
                'period' => $period,
                'error' => 'Unable to load funnel data'
            ]);
        }
    }
}
