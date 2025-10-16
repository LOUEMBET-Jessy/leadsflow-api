<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Lead\StoreLeadRequest;
use App\Http\Requests\Api\V1\Lead\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeadsExport;
use App\Imports\LeadsImport;

class LeadController extends Controller
{
    /**
     * Display a listing of leads
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::with(['status', 'assignedTo', 'pipelineStage', 'createdBy']);

        // Apply filters
        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_to_user_id')) {
            $query->where('assigned_to_user_id', $request->assigned_to_user_id);
        }

        if ($request->has('pipeline_stage_id')) {
            $query->where('pipeline_stage_id', $request->pipeline_stage_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $leads = $query->paginate($perPage);

        return response()->json($leads);
    }

    /**
     * Store a newly created lead
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = Lead::create([
            ...$request->validated(),
            'created_by_user_id' => $request->user()->id,
        ]);

        $lead->load(['status', 'assignedTo', 'pipelineStage', 'createdBy']);

        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => $lead,
        ], 201);
    }

    /**
     * Display the specified lead
     */
    public function show(Lead $lead): JsonResponse
    {
        $lead->load([
            'status', 
            'assignedTo', 
            'pipelineStage', 
            'createdBy',
            'tasks' => function ($query) {
                $query->orderBy('due_date', 'asc');
            },
            'interactions' => function ($query) {
                $query->with('user')->orderBy('interaction_date', 'desc');
            },
            'aiInsights' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return response()->json([
            'lead' => $lead
        ]);
    }

    /**
     * Update the specified lead
     */
    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        $lead->update($request->validated());
        $lead->load(['status', 'assignedTo', 'pipelineStage', 'createdBy']);

        return response()->json([
            'message' => 'Lead updated successfully',
            'lead' => $lead,
        ]);
    }

    /**
     * Remove the specified lead
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json([
            'message' => 'Lead deleted successfully',
        ]);
    }

    /**
     * Update lead status
     */
    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        $request->validate([
            'status_id' => 'required|exists:lead_statuses,id',
        ]);

        $lead->update(['status_id' => $request->status_id]);

        return response()->json([
            'message' => 'Lead status updated successfully',
            'lead' => $lead->load('status'),
        ]);
    }

    /**
     * Assign lead to user
     */
    public function assign(Request $request, Lead $lead): JsonResponse
    {
        $request->validate([
            'assigned_to_user_id' => 'required|exists:users,id',
        ]);

        $lead->update(['assigned_to_user_id' => $request->assigned_to_user_id]);

        return response()->json([
            'message' => 'Lead assigned successfully',
            'lead' => $lead->load('assignedTo'),
        ]);
    }

    /**
     * Import leads from CSV/Excel
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            Excel::import(new LeadsImport, $request->file('file'));
            
            return response()->json([
                'message' => 'Leads imported successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export leads
     */
    public function export(Request $request): JsonResponse
    {
        $format = $request->get('format', 'csv');
        $filters = $request->only(['status_id', 'source', 'priority', 'assigned_to_user_id']);

        $fileName = 'leads_export_' . now()->format('Y_m_d_H_i_s') . '.' . $format;

        try {
            Excel::store(new LeadsExport($filters), $fileName, 'public');
            
            return response()->json([
                'message' => 'Export completed successfully',
                'download_url' => asset('storage/' . $fileName),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Export failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Capture lead from web form
     */
    public function captureWebForm(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'custom_fields' => 'nullable|array',
        ]);

        // Get default status and pipeline stage
        $defaultStatus = LeadStatus::where('name', 'Nouveau')->first();
        $defaultStage = PipelineStage::whereHas('pipeline', function ($query) {
            $query->where('is_default', true);
        })->orderBy('order')->first();

        $lead = Lead::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'source' => $request->source ?? 'Web Form',
            'status_id' => $defaultStatus->id,
            'pipeline_stage_id' => $defaultStage->id ?? null,
            'custom_fields' => $request->custom_fields,
            'created_by_user_id' => 1, // System user or first admin
        ]);

        return response()->json([
            'message' => 'Lead captured successfully',
            'lead_id' => $lead->id,
        ], 201);
    }

    /**
     * Capture lead from email
     */
    public function captureEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email_content' => 'required|string',
            'from_email' => 'required|email',
            'subject' => 'required|string',
        ]);

        // Parse email content to extract lead information
        $emailContent = $request->email_content;
        $fromEmail = $request->from_email;
        $subject = $request->subject;

        // Simple email parsing (in production, use a more sophisticated parser)
        $name = $this->extractNameFromEmail($fromEmail);
        $phone = $this->extractPhoneFromContent($emailContent);
        $company = $this->extractCompanyFromContent($emailContent);

        $defaultStatus = LeadStatus::where('name', 'Nouveau')->first();
        $defaultStage = PipelineStage::whereHas('pipeline', function ($query) {
            $query->where('is_default', true);
        })->orderBy('order')->first();

        $lead = Lead::create([
            'first_name' => $name['first_name'],
            'last_name' => $name['last_name'],
            'email' => $fromEmail,
            'phone' => $phone,
            'company' => $company,
            'source' => 'Email',
            'status_id' => $defaultStatus->id,
            'pipeline_stage_id' => $defaultStage->id ?? null,
            'notes' => "Captured from email: {$subject}\n\n{$emailContent}",
            'created_by_user_id' => 1,
        ]);

        return response()->json([
            'message' => 'Lead captured from email successfully',
            'lead_id' => $lead->id,
        ], 201);
    }

    /**
     * Recalculate lead score
     */
    public function recalculateScore(Lead $lead): JsonResponse
    {
        // Simple scoring algorithm (in production, use AI/ML)
        $score = 0;
        
        // Base score
        $score += 10;
        
        // Company size bonus
        if ($lead->company_size) {
            $score += match ($lead->company_size) {
                'Large' => 20,
                'Medium' => 15,
                'Small' => 10,
                default => 5,
            };
        }
        
        // Priority bonus
        $score += match ($lead->priority) {
            'Hot' => 30,
            'Warm' => 15,
            'Cold' => 5,
            default => 10,
        };
        
        // Recent interaction bonus
        if ($lead->last_contact_date && $lead->last_contact_date->diffInDays(now()) <= 7) {
            $score += 10;
        }

        $lead->update(['score' => $score]);

        return response()->json([
            'message' => 'Lead score recalculated successfully',
            'score' => $score,
        ]);
    }

    /**
     * Extract name from email address
     */
    private function extractNameFromEmail(string $email): array
    {
        $localPart = explode('@', $email)[0];
        $nameParts = explode('.', $localPart);
        
        return [
            'first_name' => ucfirst($nameParts[0] ?? 'Unknown'),
            'last_name' => ucfirst($nameParts[1] ?? ''),
        ];
    }

    /**
     * Extract phone number from content
     */
    private function extractPhoneFromContent(string $content): ?string
    {
        preg_match('/\+?[\d\s\-\(\)]{10,}/', $content, $matches);
        return $matches[0] ?? null;
    }

    /**
     * Extract company from content
     */
    private function extractCompanyFromContent(string $content): ?string
    {
        // Simple company extraction (in production, use NLP)
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if (stripos($line, 'company') !== false || stripos($line, 'entreprise') !== false) {
                return trim($line);
            }
        }
        return null;
    }
}
