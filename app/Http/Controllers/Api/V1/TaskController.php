<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Get all tasks for the account
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::whereHas('user', function ($q) use ($request) {
            $q->where('account_id', $request->user()->account_id);
        })->with(['lead', 'user']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('overdue')) {
            if ($request->overdue) {
                $query->where('due_date', '<', now())
                      ->whereIn('status', ['EnCours', 'Retard']);
            }
        }

        if ($request->has('due_today')) {
            if ($request->due_today) {
                $query->whereDate('due_date', today())
                      ->whereIn('status', ['EnCours', 'Retard']);
            }
        }

        // Tri
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->paginate($request->get('per_page', 20));

        return response()->json($tasks);
    }

    /**
     * Get a specific task
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        // Vérifier que la tâche appartient au compte
        if ($task->user->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->load(['lead', 'user']);

        return response()->json(['task' => $task]);
    }

    /**
     * Create a new task
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'required|date|after:now',
            'reminders' => 'nullable|array',
        ]);

        // Vérifier que le lead appartient au compte si spécifié
        if ($request->lead_id) {
            $lead = Lead::findOrFail($request->lead_id);
            if ($lead->account_id !== $request->user()->account_id) {
                return response()->json(['message' => 'Lead not found'], 404);
            }
        }

        $task = Task::create([
            'lead_id' => $request->lead_id,
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'reminders' => $request->reminders,
        ]);

        $task->load(['lead', 'user']);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    /**
     * Update a task
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        // Vérifier que la tâche appartient au compte
        if ($task->user->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:EnCours,Retard,Complete,Cancelled',
            'due_date' => 'sometimes|date',
            'completion_notes' => 'nullable|string',
            'reminders' => 'nullable|array',
        ]);

        $task->update($request->only([
            'title', 'description', 'priority', 'status', 'due_date', 'completion_notes', 'reminders'
        ]));

        // Si la tâche est marquée comme complète, mettre à jour completed_at
        if ($request->status === 'Complete' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        $task->load(['lead', 'user']);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Delete a task
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        // Vérifier que la tâche appartient au compte
        if ($task->user->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Mark task as complete
     */
    public function complete(Request $request, Task $task): JsonResponse
    {
        // Vérifier que la tâche appartient au compte
        if ($task->user->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        $task->update([
            'status' => 'Complete',
            'completed_at' => now(),
            'completion_notes' => $request->completion_notes,
        ]);

        $task->load(['lead', 'user']);

        return response()->json([
            'message' => 'Task completed successfully',
            'task' => $task,
        ]);
    }

    /**
     * Get task statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;

        $stats = [
            'total' => Task::whereHas('user', function ($q) use ($accountId) {
                $q->where('account_id', $accountId);
            })->count(),
            'by_status' => Task::whereHas('user', function ($q) use ($accountId) {
                $q->where('account_id', $accountId);
            })->selectRaw('status, count(*) as count')
              ->groupBy('status')
              ->get()
              ->pluck('count', 'status'),
            'by_priority' => Task::whereHas('user', function ($q) use ($accountId) {
                $q->where('account_id', $accountId);
            })->selectRaw('priority, count(*) as count')
              ->groupBy('priority')
              ->get()
              ->pluck('count', 'priority'),
            'overdue' => Task::whereHas('user', function ($q) use ($accountId) {
                $q->where('account_id', $accountId);
            })->where('due_date', '<', now())
              ->whereIn('status', ['EnCours', 'Retard'])
              ->count(),
            'due_today' => Task::whereHas('user', function ($q) use ($accountId) {
                $q->where('account_id', $accountId);
            })->whereDate('due_date', today())
              ->whereIn('status', ['EnCours', 'Retard'])
              ->count(),
            'completed_this_week' => Task::whereHas('user', function ($q) use ($accountId) {
                $q->where('account_id', $accountId);
            })->where('status', 'Complete')
              ->where('completed_at', '>=', now()->startOfWeek())
              ->count(),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get user's tasks
     */
    public function myTasks(Request $request): JsonResponse
    {
        $query = $request->user()->tasks()->with('lead');

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('overdue')) {
            if ($request->overdue) {
                $query->where('due_date', '<', now())
                      ->whereIn('status', ['EnCours', 'Retard']);
            }
        }

        // Tri
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->paginate($request->get('per_page', 20));

        return response()->json($tasks);
    }

    /**
     * Get overdue tasks
     */
    public function overdue(Request $request): JsonResponse
    {
        $tasks = Task::whereHas('user', function ($q) use ($request) {
            $q->where('account_id', $request->user()->account_id);
        })
        ->where('due_date', '<', now())
        ->whereIn('status', ['EnCours', 'Retard'])
        ->with(['lead', 'user'])
        ->orderBy('due_date', 'asc')
        ->get();

        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Get tasks due today
     */
    public function dueToday(Request $request): JsonResponse
    {
        $tasks = Task::whereHas('user', function ($q) use ($request) {
            $q->where('account_id', $request->user()->account_id);
        })
        ->whereDate('due_date', today())
        ->whereIn('status', ['EnCours', 'Retard'])
        ->with(['lead', 'user'])
        ->orderBy('due_date', 'asc')
        ->get();

        return response()->json(['tasks' => $tasks]);
    }
}
