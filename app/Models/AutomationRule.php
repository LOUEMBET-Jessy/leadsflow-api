<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'trigger_type',
        'action_type',
        'parameters',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
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

    public function scopeByTrigger($query, $triggerType)
    {
        return $query->where('trigger_type', $triggerType);
    }

    public function scopeByAction($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority')->orderBy('created_at');
    }

    // Méthodes utilitaires
    public function canExecute($lead, $context = [])
    {
        if (!$this->is_active) {
            return false;
        }

        $conditions = $this->parameters['conditions'] ?? [];
        
        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($lead, $condition, $context)) {
                return false;
            }
        }

        return true;
    }

    protected function evaluateCondition($lead, $condition, $context)
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        $leadValue = $this->getFieldValue($lead, $field, $context);

        return match($operator) {
            'equals' => $leadValue == $value,
            'not_equals' => $leadValue != $value,
            'greater_than' => $leadValue > $value,
            'less_than' => $leadValue < $value,
            'contains' => str_contains(strtolower($leadValue), strtolower($value)),
            'not_contains' => !str_contains(strtolower($leadValue), strtolower($value)),
            'in' => in_array($leadValue, (array)$value),
            'not_in' => !in_array($leadValue, (array)$value),
            default => false
        };
    }

    protected function getFieldValue($lead, $field, $context)
    {
        // Champs directs du lead
        if (in_array($field, ['name', 'email', 'phone', 'company', 'status', 'source', 'score'])) {
            return $lead->$field;
        }

        // Champs de relation
        if ($field === 'stage_name') {
            return $lead->currentStage?->name;
        }

        if ($field === 'pipeline_name') {
            return $lead->currentStage?->pipeline?->name;
        }

        // Champs de contexte (pour les triggers)
        if (isset($context[$field])) {
            return $context[$field];
        }

        return null;
    }

    public function execute($lead, $context = [])
    {
        $actionType = $this->action_type;
        $actionParams = $this->parameters['action'] ?? [];

        return match($actionType) {
            'assign_user' => $this->assignUser($lead, $actionParams),
            'update_status' => $this->updateStatus($lead, $actionParams),
            'update_stage' => $this->updateStage($lead, $actionParams),
            'send_email' => $this->sendEmail($lead, $actionParams),
            'create_task' => $this->createTask($lead, $actionParams),
            'update_score' => $this->updateScore($lead, $actionParams),
            default => false
        };
    }

    protected function assignUser($lead, $params)
    {
        $userId = $params['user_id'] ?? null;
        if (!$userId) return false;

        $lead->assignedUsers()->syncWithoutDetaching([$userId => [
            'assigned_at' => now(),
            'assigned_by_user_id' => null, // Système
            'notes' => 'Assigné automatiquement par règle: ' . $this->name
        ]]);

        return true;
    }

    protected function updateStatus($lead, $params)
    {
        $status = $params['status'] ?? null;
        if (!$status) return false;

        $lead->update(['status' => $status]);
        return true;
    }

    protected function updateStage($lead, $params)
    {
        $stageId = $params['stage_id'] ?? null;
        if (!$stageId) return false;

        $lead->update(['current_stage_id' => $stageId]);
        return true;
    }

    protected function sendEmail($lead, $params)
    {
        // Logique d'envoi d'email
        return true;
    }

    protected function createTask($lead, $params)
    {
        $lead->tasks()->create([
            'user_id' => $params['user_id'] ?? $lead->assignedUsers->first()?->id,
            'title' => $params['title'] ?? 'Tâche automatique',
            'description' => $params['description'] ?? '',
            'priority' => $params['priority'] ?? 'medium',
            'due_date' => now()->addDays($params['due_in_days'] ?? 1),
        ]);

        return true;
    }

    protected function updateScore($lead, $params)
    {
        $scoreChange = $params['score_change'] ?? 0;
        $newScore = max(0, min(100, $lead->score + $scoreChange));
        
        $lead->update(['score' => $newScore]);
        return true;
    }
}
