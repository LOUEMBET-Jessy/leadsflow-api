<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'title',
        'source',
        'status_id',
        'pipeline_stage_id',
        'assigned_to_user_id',
        'score',
        'priority',
        'last_contact_date',
        'notes',
        'address',
        'city',
        'country',
        'industry',
        'company_size',
        'custom_fields',
        'created_by_user_id',
    ];

    protected $casts = [
        'last_contact_date' => 'datetime',
        'custom_fields' => 'array',
    ];

    /**
     * Get the user assigned to the lead.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Get the user who created the lead.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the status of the lead.
     */
    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    /**
     * Get the pipeline stage of the lead.
     */
    public function pipelineStage()
    {
        return $this->belongsTo(PipelineStage::class);
    }

    /**
     * Get the tasks for the lead.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the interactions for the lead.
     */
    public function interactions()
    {
        return $this->hasMany(Interaction::class)->orderBy('interaction_date', 'desc');
    }

    /**
     * Get the AI insights for the lead.
     */
    public function aiInsights()
    {
        return $this->hasMany(AiInsight::class);
    }

    /**
     * Get the full name of the lead.
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope a query to only include leads by status.
     */
    public function scopeByStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    /**
     * Scope a query to only include leads by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include leads by assigned user.
     */
    public function scopeByAssignedUser($query, $userId)
    {
        return $query->where('assigned_to_user_id', $userId);
    }

    /**
     * Scope a query to only include leads by source.
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }
}
