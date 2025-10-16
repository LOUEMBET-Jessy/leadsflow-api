<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'is_active',
        'trigger_conditions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trigger_conditions' => 'array',
    ];

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function steps()
    {
        return $this->hasMany(SequenceStep::class)->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasMany(SequenceEnrollment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Méthodes utilitaires
    public function canEnrollLead($lead)
    {
        if (!$this->is_active) {
            return false;
        }

        $conditions = $this->trigger_conditions ?? [];
        
        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($lead, $condition)) {
                return false;
            }
        }

        return true;
    }

    protected function evaluateCondition($lead, $condition)
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        $leadValue = $this->getFieldValue($lead, $field);

        return match($operator) {
            'equals' => $leadValue == $value,
            'not_equals' => $leadValue != $value,
            'greater_than' => $leadValue > $value,
            'less_than' => $leadValue < $value,
            'contains' => str_contains(strtolower($leadValue), strtolower($value)),
            'in' => in_array($leadValue, (array)$value),
            default => false
        };
    }

    protected function getFieldValue($lead, $field)
    {
        return match($field) {
            'status' => $lead->status,
            'source' => $lead->source,
            'score' => $lead->score,
            'stage_name' => $lead->currentStage?->name,
            'pipeline_name' => $lead->currentStage?->pipeline?->name,
            'company' => $lead->company,
            'location' => $lead->location,
            default => null
        };
    }

    public function enrollLead($lead)
    {
        if (!$this->canEnrollLead($lead)) {
            return false;
        }

        // Vérifier si le lead n'est pas déjà inscrit
        $existingEnrollment = $this->enrollments()
            ->where('lead_id', $lead->id)
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if ($existingEnrollment) {
            return $existingEnrollment;
        }

        return $this->enrollments()->create([
            'lead_id' => $lead->id,
            'status' => 'active',
            'started_at' => now(),
            'current_step' => 0,
        ]);
    }

    public function getTotalEnrollmentsAttribute()
    {
        return $this->enrollments()->count();
    }

    public function getActiveEnrollmentsAttribute()
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    public function getCompletionRateAttribute()
    {
        $total = $this->enrollments()->count();
        if ($total === 0) return 0;

        $completed = $this->enrollments()->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 2);
    }
}
