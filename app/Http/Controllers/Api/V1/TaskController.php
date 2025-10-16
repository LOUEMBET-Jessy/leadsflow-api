<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Task\StoreTaskRequest;
use App\Http\Requests\Api\V1\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::with(['assignedTo', 'createdBy', 'lead']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_to_user_id')) {
            $query->where('assigned_to_user_id', $request->assigned_to_user_id);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('due_date_from')) {
            $query->where('due_date', '>=', $request->due_date_from);
        }

        if ($request->has('due_date_to')) {
            $query->where('due_date', '<=', $request->due_date_to);
        }

        if ($request->has('overdue')) {
            $query->where('due_date', '<', now())
                  ->where('status', '!=', 'completed');
        }

        if ($request->has('due_today')) {
            $query->whereDate('due_date', today())
                  ->where('status', '!=', 'completed');
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tasks = $query->paginate($perPage);

        return response()->json($tasks);
    }

    /**
     * Store a newly created task
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            ...$request->validated(),
            'created_by_user_id' => $request->user()->id,
        ]);

        $task->load(['assignedTo', 'createdBy', 'lead']);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    /**
     * Display the specified task
     */
    public function show(Task $task): JsonResponse
    {
        $task->load(['assignedTo', 'createdBy', 'lead']);

        return response()->json([
            'task' => $task
        ]);
    }

    /**
     * Update the specified task
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());
        $task->load(['assignedTo', 'createdBy', 'lead']);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Remove the specified task
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Mark task as completed
     */
    public function complete(Request $request, Task $task): JsonResponse
    {
        $task->update([
            'status' => 'completed',
            'completion_date' => now(),
        ]);

        return response()->json([
            'message' => 'Task marked as completed',
            'task' => $task->load(['assignedTo', 'createdBy', 'lead']),
        ]);
    }

    /**
     * Get task statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = Carbon::now();

        $stats = [
            'total' => Task::where('assigned_to_user_id', $user->id)->count(),
            'completed' => Task::where('assigned_to_user_id', $user->id)
                ->where('status', 'completed')->count(),
            'pending' => Task::where('assigned_to_user_id', $user->id)
                ->where('status', 'todo')->count(),
            'in_progress' => Task::where('assigned_to_user_id', $user->id)
                ->where('status', 'in_progress')->count(),
            'overdue' => Task::where('assigned_to_user_id', $user->id)
                ->where('due_date', '<', $now)
                ->where('status', '!=', 'completed')->count(),
            'due_today' => Task::where('assigned_to_user_id', $user->id)
                ->whereDate('due_date', $now->toDateString())
                ->where('status', '!=', 'completed')->count(),
        ];

        // Completion rate
        $stats['completion_rate'] = $stats['total'] > 0 
            ? round(($stats['completed'] / $stats['total']) * 100, 2) 
            : 0;

        // Tasks by priority
        $stats['by_priority'] = Task::where('assigned_to_user_id', $user->id)
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority');

        // Tasks by status
        $stats['by_status'] = Task::where('assigned_to_user_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Recent completion trend (last 30 days)
        $stats['completion_trend'] = Task::where('assigned_to_user_id', $user->id)
            ->where('status', 'completed')
            ->where('completion_date', '>=', $now->copy()->subDays(30))
            ->selectRaw('DATE(completion_date) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'statistics' => $stats
        ]);
    }

    /**
     * Get overdue tasks
     */
    public function overdue(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = Carbon::now();

        $tasks = Task::where('assigned_to_user_id', $user->id)
            ->where('due_date', '<', $now)
            ->where('status', '!=', 'completed')
            ->with(['lead', 'createdBy'])
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    /**
     * Get tasks due today
     */
    public function dueToday(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();

        $tasks = Task::where('assigned_to_user_id', $user->id)
            ->whereDate('due_date', $today)
            ->where('status', '!=', 'completed')
            ->with(['lead', 'createdBy'])
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    /**
     * Get tasks by lead
     */
    public function byLead(Lead $lead): JsonResponse
    {
        $tasks = $lead->tasks()
            ->with(['assignedTo', 'createdBy'])
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'lead' => $lead,
            'tasks' => $tasks
        ]);
    }

    /**
     * Bulk update task status
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'status' => 'required|in:todo,in_progress,completed',
        ]);

        $updated = Task::whereIn('id', $request->task_ids)
            ->update([
                'status' => $request->status,
                'completion_date' => $request->status === 'completed' ? now() : null,
            ]);

        return response()->json([
            'message' => "{$updated} tasks updated successfully",
        ]);
    }

    /**
     * Set task reminders
     */
    public function setReminders(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'reminders' => 'required|array',
            'reminders.*' => 'date|after:now',
        ]);

        $task->update(['reminders' => $request->reminders]);

        return response()->json([
            'message' => 'Reminders set successfully',
            'task' => $task,
        ]);
    }
}
