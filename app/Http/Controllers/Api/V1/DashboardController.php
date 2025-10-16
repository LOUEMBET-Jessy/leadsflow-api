<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Interaction;
use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard overview
     */
    public function overview(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $userId = $request->user()->id;

        // Statistiques générales
        $stats = [
            'leads' => [
                'total' => Lead::where('account_id', $accountId)->count(),
                'new_today' => Lead::where('account_id', $accountId)
                    ->whereDate('created_at', today())
                    ->count(),
                'assigned_to_me' => Lead::where('account_id', $accountId)
                    ->whereHas('assignedUsers', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->count(),
            ],
            'tasks' => [
                'total' => Task::whereHas('user', function ($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                })->count(),
                'my_tasks' => Task::where('user_id', $userId)->count(),
                'overdue' => Task::where('user_id', $userId)
                    ->where('due_date', '<', now())
                    ->whereIn('status', ['EnCours', 'Retard'])
                    ->count(),
                'due_today' => Task::where('user_id', $userId)
                    ->whereDate('due_date', today())
                    ->whereIn('status', ['EnCours', 'Retard'])
                    ->count(),
            ],
            'interactions' => [
                'today' => Interaction::whereHas('lead', function ($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                })->whereDate('date', today())->count(),
                'this_week' => Interaction::whereHas('lead', function ($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                })->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ],
        ];

        return response()->json(['overview' => $stats]);
    }

    /**
     * Get leads by status
     */
    public function leadsByStatus(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;

        $leadsByStatus = Lead::where('account_id', $accountId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return response()->json(['leads_by_status' => $leadsByStatus]);
    }

    /**
     * Get leads by source
     */
    public function leadsBySource(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;

        $leadsBySource = Lead::where('account_id', $accountId)
            ->selectRaw('source, count(*) as count')
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json(['leads_by_source' => $leadsBySource]);
    }

    /**
     * Get pipeline funnel data
     */
    public function pipelineFunnel(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $pipelineId = $request->get('pipeline_id');

        $query = Lead::where('account_id', $accountId)
            ->join('stages', 'leads.current_stage_id', '=', 'stages.id')
            ->join('pipelines', 'stages.pipeline_id', '=', 'pipelines.id');

        if ($pipelineId) {
            $query->where('pipelines.id', $pipelineId);
        }

        $funnelData = $query->select(
            'stages.name as stage_name',
            'stages.order as stage_order',
            'pipelines.name as pipeline_name',
            DB::raw('count(*) as leads_count')
        )
        ->groupBy('stages.id', 'stages.name', 'stages.order', 'pipelines.name')
        ->orderBy('stages.order')
        ->get();

        return response()->json(['funnel_data' => $funnelData]);
    }

    /**
     * Get recent activities
     */
    public function recentActivities(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $limit = $request->get('limit', 20);

        $activities = collect();

        // Leads récents
        $recentLeads = Lead::where('account_id', $accountId)
            ->with(['currentStage', 'assignedUsers'])
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

        // Tâches récentes
        $recentTasks = Task::whereHas('user', function ($q) use ($accountId) {
            $q->where('account_id', $accountId);
        })
        ->with(['lead', 'user'])
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

        // Interactions récentes
        $recentInteractions = Interaction::whereHas('lead', function ($q) use ($accountId) {
            $q->where('account_id', $accountId);
        })
        ->with(['lead', 'user'])
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

        return response()->json(['activities' => $activities->values()]);
    }

    /**
     * Get team performance
     */
    public function teamPerformance(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $period = $request->get('period', 'month');

        $startDate = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        $teamPerformance = User::where('account_id', $accountId)
            ->withCount([
                'assignedLeads as leads_count',
                'interactions as interactions_count',
                'tasks as tasks_count',
                'tasks as completed_tasks_count' => function ($query) use ($startDate) {
                    $query->where('status', 'Complete')
                          ->where('completed_at', '>=', $startDate);
                }
            ])
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'role' => $user->role,
                    'leads_count' => $user->leads_count,
                    'interactions_count' => $user->interactions_count,
                    'tasks_count' => $user->tasks_count,
                    'completed_tasks_count' => $user->completed_tasks_count,
                ];
            });

        return response()->json(['team_performance' => $teamPerformance]);
    }

    /**
     * Get conversion rates
     */
    public function conversionRates(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $period = $request->get('period', 'month');

        $startDate = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        $conversionRates = Pipeline::where('account_id', $accountId)
            ->with(['stages' => function ($query) {
                $query->orderBy('order');
            }])
            ->get()
            ->map(function ($pipeline) use ($startDate) {
                $totalLeads = $pipeline->leads()
                    ->where('created_at', '>=', $startDate)
                    ->count();

                $stages = $pipeline->stages->map(function ($stage) use ($startDate, $totalLeads) {
                    $stageLeads = $stage->leads()
                        ->where('created_at', '>=', $startDate)
                        ->count();

                    return [
                        'stage_name' => $stage->name,
                        'leads_count' => $stageLeads,
                        'conversion_rate' => $totalLeads > 0 ? round(($stageLeads / $totalLeads) * 100, 2) : 0,
                    ];
                });

                return [
                    'pipeline_name' => $pipeline->name,
                    'total_leads' => $totalLeads,
                    'stages' => $stages,
                ];
            });

        return response()->json(['conversion_rates' => $conversionRates]);
    }

    /**
     * Get monthly trends
     */
    public function monthlyTrends(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $months = $request->get('months', 6);

        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $trends[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->format('F Y'),
                'leads_created' => Lead::where('account_id', $accountId)
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'leads_won' => Lead::where('account_id', $accountId)
                    ->where('status', 'Gagné')
                    ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'interactions' => Interaction::whereHas('lead', function ($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                })
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->count(),
            ];
        }

        return response()->json(['monthly_trends' => $trends]);
    }

    /**
     * Get top performing sources
     */
    public function topSources(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $limit = $request->get('limit', 10);

        $topSources = Lead::where('account_id', $accountId)
            ->selectRaw('source, count(*) as leads_count, avg(score) as avg_score')
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderBy('leads_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['top_sources' => $topSources]);
    }

    /**
     * Get overdue tasks
     */
    public function overdueTasks(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $userId = $request->get('user_id');

        $query = Task::whereHas('user', function ($q) use ($accountId) {
            $q->where('account_id', $accountId);
        })
        ->where('due_date', '<', now())
        ->whereIn('status', ['EnCours', 'Retard'])
        ->with(['lead', 'user']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $overdueTasks = $query->orderBy('due_date', 'asc')->get();

        return response()->json(['overdue_tasks' => $overdueTasks]);
    }
}
