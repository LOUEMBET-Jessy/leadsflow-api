<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Pipeline\StorePipelineRequest;
use App\Http\Requests\Api\V1\Pipeline\UpdatePipelineRequest;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PipelineController extends Controller
{
    /**
     * Display a listing of pipelines
     */
    public function index(): JsonResponse
    {
        $pipelines = Pipeline::with(['stages', 'createdBy'])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'pipelines' => $pipelines
        ]);
    }

    /**
     * Store a newly created pipeline
     */
    public function store(StorePipelineRequest $request): JsonResponse
    {
        // If this is set as default, unset other defaults
        if ($request->is_default) {
            Pipeline::where('is_default', true)->update(['is_default' => false]);
        }

        $pipeline = Pipeline::create([
            ...$request->validated(),
            'created_by_user_id' => $request->user()->id,
        ]);

        $pipeline->load(['stages', 'createdBy']);

        return response()->json([
            'message' => 'Pipeline created successfully',
            'pipeline' => $pipeline,
        ], 201);
    }

    /**
     * Display the specified pipeline
     */
    public function show(Pipeline $pipeline): JsonResponse
    {
        $pipeline->load(['stages' => function ($query) {
            $query->orderBy('order');
        }, 'createdBy']);

        return response()->json([
            'pipeline' => $pipeline
        ]);
    }

    /**
     * Update the specified pipeline
     */
    public function update(UpdatePipelineRequest $request, Pipeline $pipeline): JsonResponse
    {
        // If this is set as default, unset other defaults
        if ($request->is_default) {
            Pipeline::where('is_default', true)
                ->where('id', '!=', $pipeline->id)
                ->update(['is_default' => false]);
        }

        $pipeline->update($request->validated());
        $pipeline->load(['stages', 'createdBy']);

        return response()->json([
            'message' => 'Pipeline updated successfully',
            'pipeline' => $pipeline,
        ]);
    }

    /**
     * Remove the specified pipeline
     */
    public function destroy(Pipeline $pipeline): JsonResponse
    {
        // Check if pipeline has leads
        if ($pipeline->leads()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete pipeline with existing leads',
            ], 422);
        }

        $pipeline->delete();

        return response()->json([
            'message' => 'Pipeline deleted successfully',
        ]);
    }

    /**
     * Get pipeline stages
     */
    public function stages(Pipeline $pipeline): JsonResponse
    {
        $stages = $pipeline->stages()->orderBy('order')->get();

        return response()->json([
            'stages' => $stages
        ]);
    }

    /**
     * Add stage to pipeline
     */
    public function addStage(Request $request, Pipeline $pipeline): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'order' => 'nullable|integer|min:0',
        ]);

        $order = $request->order ?? $pipeline->stages()->max('order') + 1;

        $stage = $pipeline->stages()->create([
            'name' => $request->name,
            'color_code' => $request->color_code ?? '#0066CC',
            'order' => $order,
        ]);

        return response()->json([
            'message' => 'Stage added successfully',
            'stage' => $stage,
        ], 201);
    }

    /**
     * Update pipeline stage
     */
    public function updateStage(Request $request, Pipeline $pipeline, PipelineStage $stage): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'order' => 'nullable|integer|min:0',
        ]);

        $stage->update($request->only(['name', 'color_code', 'order']));

        return response()->json([
            'message' => 'Stage updated successfully',
            'stage' => $stage,
        ]);
    }

    /**
     * Remove pipeline stage
     */
    public function removeStage(Pipeline $pipeline, PipelineStage $stage): JsonResponse
    {
        // Check if stage has leads
        if ($stage->leads()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete stage with existing leads',
            ], 422);
        }

        $stage->delete();

        return response()->json([
            'message' => 'Stage removed successfully',
        ]);
    }

    /**
     * Get pipeline view (Kanban)
     */
    public function pipelineView(Request $request, Pipeline $pipeline): JsonResponse
    {
        $stages = $pipeline->stages()->orderBy('order')->get();
        
        $leadsByStage = [];
        
        foreach ($stages as $stage) {
            $leadsQuery = $stage->leads()->with(['status', 'assignedTo', 'createdBy']);
            
            // Apply filters if provided
            if ($request->has('assigned_to_user_id')) {
                $leadsQuery->where('assigned_to_user_id', $request->assigned_to_user_id);
            }
            
            if ($request->has('priority')) {
                $leadsQuery->where('priority', $request->priority);
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $leadsQuery->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%");
                });
            }
            
            $leads = $leadsQuery->orderBy('updated_at', 'desc')->get();
            
            $leadsByStage[] = [
                'stage' => $stage,
                'leads' => $leads,
                'count' => $leads->count(),
            ];
        }

        return response()->json([
            'pipeline' => $pipeline,
            'stages_with_leads' => $leadsByStage,
        ]);
    }

    /**
     * Move lead between stages
     */
    public function moveLead(Request $request): JsonResponse
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        $lead = \App\Models\Lead::findOrFail($request->lead_id);
        $stage = PipelineStage::findOrFail($request->stage_id);

        $lead->update(['pipeline_stage_id' => $stage->id]);

        return response()->json([
            'message' => 'Lead moved successfully',
            'lead' => $lead->load(['pipelineStage', 'status', 'assignedTo']),
        ]);
    }
}
