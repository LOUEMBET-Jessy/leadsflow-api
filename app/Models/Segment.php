<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'criteria',
        'is_active',
        'lead_count',
        'last_updated_at',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Méthodes utilitaires
    public function getLeads()
    {
        $query = Lead::where('account_id', $this->account_id);

        foreach ($this->criteria as $criterion) {
            $query = $this->applyCriterion($query, $criterion);
        }

        return $query->get();
    }

    protected function applyCriterion($query, $criterion)
    {
        $field = $criterion['field'];
        $operator = $criterion['operator'];
        $value = $criterion['value'];

        return match($operator) {
            'equals' => $query->where($field, $value),
            'not_equals' => $query->where($field, '!=', $value),
            'greater_than' => $query->where($field, '>', $value),
            'less_than' => $query->where($field, '<', $value),
            'greater_than_or_equal' => $query->where($field, '>=', $value),
            'less_than_or_equal' => $query->where($field, '<=', $value),
            'contains' => $query->where($field, 'like', '%' . $value . '%'),
            'not_contains' => $query->where($field, 'not like', '%' . $value . '%'),
            'in' => $query->whereIn($field, (array)$value),
            'not_in' => $query->whereNotIn($field, (array)$value),
            'is_null' => $query->whereNull($field),
            'is_not_null' => $query->whereNotNull($field),
            'between' => $query->whereBetween($field, $value),
            'not_between' => $query->whereNotBetween($field, $value),
            'date_between' => $query->whereBetween($field, $value),
            'date_after' => $query->where($field, '>', $value),
            'date_before' => $query->where($field, '<', $value),
            default => $query
        };
    }

    public function updateLeadCount()
    {
        $count = $this->getLeads()->count();
        $this->update([
            'lead_count' => $count,
            'last_updated_at' => now(),
        ]);
    }

    public function getLeadIds()
    {
        $query = Lead::where('account_id', $this->account_id);

        foreach ($this->criteria as $criterion) {
            $query = $this->applyCriterion($query, $criterion);
        }

        return $query->pluck('id');
    }

    public function addLead($lead)
    {
        if ($this->matchesLead($lead)) {
            $this->increment('lead_count');
            return true;
        }
        return false;
    }

    public function removeLead($lead)
    {
        if ($this->matchesLead($lead)) {
            $this->decrement('lead_count');
            return true;
        }
        return false;
    }

    public function matchesLead($lead)
    {
        foreach ($this->criteria as $criterion) {
            if (!$this->leadMatchesCriterion($lead, $criterion)) {
                return false;
            }
        }
        return true;
    }

    protected function leadMatchesCriterion($lead, $criterion)
    {
        $field = $criterion['field'];
        $operator = $criterion['operator'];
        $value = $criterion['value'];

        $leadValue = $this->getLeadFieldValue($lead, $field);

        return match($operator) {
            'equals' => $leadValue == $value,
            'not_equals' => $leadValue != $value,
            'greater_than' => $leadValue > $value,
            'less_than' => $leadValue < $value,
            'greater_than_or_equal' => $leadValue >= $value,
            'less_than_or_equal' => $leadValue <= $value,
            'contains' => str_contains(strtolower($leadValue), strtolower($value)),
            'not_contains' => !str_contains(strtolower($leadValue), strtolower($value)),
            'in' => in_array($leadValue, (array)$value),
            'not_in' => !in_array($leadValue, (array)$value),
            'is_null' => is_null($leadValue),
            'is_not_null' => !is_null($leadValue),
            'between' => $leadValue >= $value[0] && $leadValue <= $value[1],
            'not_between' => $leadValue < $value[0] || $leadValue > $value[1],
            'date_between' => $leadValue >= $value[0] && $leadValue <= $value[1],
            'date_after' => $leadValue > $value,
            'date_before' => $leadValue < $value,
            default => false
        };
    }

    protected function getLeadFieldValue($lead, $field)
    {
        // Champs directs
        if (in_array($field, ['name', 'email', 'phone', 'company', 'status', 'source', 'location', 'score', 'estimated_value'])) {
            return $lead->$field;
        }

        // Champs de relation
        if ($field === 'stage_name') {
            return $lead->currentStage?->name;
        }

        if ($field === 'pipeline_name') {
            return $lead->currentStage?->pipeline?->name;
        }

        // Champs calculés
        if ($field === 'days_since_created') {
            return $lead->created_at->diffInDays(now());
        }

        if ($field === 'days_since_last_contact') {
            return $lead->last_contact_at ? $lead->last_contact_at->diffInDays(now()) : null;
        }

        if ($field === 'interaction_count') {
            return $lead->interactions()->count();
        }

        if ($field === 'task_count') {
            return $lead->tasks()->count();
        }

        if ($field === 'open_task_count') {
            return $lead->tasks()->whereIn('status', ['EnCours', 'Retard'])->count();
        }

        return null;
    }

    public function getCriteriaDescriptionAttribute()
    {
        $descriptions = [];
        
        foreach ($this->criteria as $criterion) {
            $field = $criterion['field'];
            $operator = $criterion['operator'];
            $value = $criterion['value'];

            $operatorText = match($operator) {
                'equals' => 'est égal à',
                'not_equals' => 'n\'est pas égal à',
                'greater_than' => 'est supérieur à',
                'less_than' => 'est inférieur à',
                'contains' => 'contient',
                'in' => 'est dans',
                'between' => 'est entre',
                default => $operator
            };

            $descriptions[] = ucfirst($field) . ' ' . $operatorText . ' ' . (is_array($value) ? implode(' et ', $value) : $value);
        }

        return implode(' ET ', $descriptions);
    }
}
