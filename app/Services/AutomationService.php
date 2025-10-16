<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class AutomationService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Process automation triggers
     */
    public function processTriggers(string $event, $model): void
    {
        $automations = Automation::active()->get();

        foreach ($automations as $automation) {
            if ($this->shouldTrigger($automation, $event, $model)) {
                $this->executeAutomation($automation, $model);
            }
        }
    }

    /**
     * Check if automation should trigger
     */
    protected function shouldTrigger(Automation $automation, string $event, $model): bool
    {
        $conditions = $automation->conditions;
        
        // Check if event matches
        if (!in_array($event, $conditions['events'] ?? [])) {
            return false;
        }

        // Check model-specific conditions
        return $this->evaluateConditions($conditions, $model);
    }

    /**
     * Execute automation actions
     */
    protected function executeAutomation(Automation $automation, $model): void
    {
        $actions = $automation->actions;

        foreach ($actions as $action) {
            $this->executeAction($action, $model);
        }

        Log::info('Automation executed', [
            'automation_id' => $automation->id,
            'automation_name' => $automation->name,
            'model_type' => get_class($model),
            'model_id' => $model->id,
        ]);
    }

    /**
     * Execute specific action
     */
    protected function executeAction(array $action, $model): void
    {
        switch ($action['type']) {
            case 'create_task':
                $this->createTask($action, $model);
                break;
            case 'send_email':
                $this->sendEmail($action, $model);
                break;
            case 'change_status':
                $this->changeStatus($action, $model);
                break;
            case 'assign_lead':
                $this->assignLead($action, $model);
                break;
            case 'send_notification':
                $this->sendNotification($action, $model);
                break;
            case 'update_score':
                $this->updateScore($action, $model);
                break;
            case 'create_interaction':
                $this->createInteraction($action, $model);
                break;
        }
    }

    /**
     * Evaluate conditions
     */
    protected function evaluateConditions(array $conditions, $model): bool
    {
        $rules = $conditions['rules'] ?? [];

        foreach ($rules as $rule) {
            if (!$this->evaluateRule($rule, $model)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate single rule
     */
    protected function evaluateRule(array $rule, $model): bool
    {
        $field = $rule['field'];
        $operator = $rule['operator'];
        $value = $rule['value'];

        $modelValue = $this->getModelValue($model, $field);

        return match ($operator) {
            'equals' => $modelValue == $value,
            'not_equals' => $modelValue != $value,
            'contains' => str_contains($modelValue, $value),
            'not_contains' => !str_contains($modelValue, $value),
            'greater_than' => $modelValue > $value,
            'less_than' => $modelValue < $value,
            'is_empty' => empty($modelValue),
            'is_not_empty' => !empty($modelValue),
            'in' => in_array($modelValue, (array) $value),
            'not_in' => !in_array($modelValue, (array) $value),
            default => false,
        };
    }

    /**
     * Get model value by field path
     */
    protected function getModelValue($model, string $field)
    {
        $fields = explode('.', $field);
        $value = $model;

        foreach ($fields as $fieldName) {
            if (is_object($value) && isset($value->$fieldName)) {
                $value = $value->$fieldName;
            } elseif (is_array($value) && isset($value[$fieldName])) {
                $value = $value[$fieldName];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Create task action
     */
    protected function createTask(array $action, $model): void
    {
        $taskData = [
            'title' => $this->replacePlaceholders($action['title'], $model),
            'description' => $this->replacePlaceholders($action['description'] ?? '', $model),
            'due_date' => $this->calculateDueDate($action['due_date'] ?? '+1 day'),
            'priority' => $action['priority'] ?? 'medium',
            'created_by_user_id' => $model->created_by_user_id ?? 1,
        ];

        if ($model instanceof Lead) {
            $taskData['lead_id'] = $model->id;
            $taskData['assigned_to_user_id'] = $model->assigned_to_user_id ?? $model->created_by_user_id;
        }

        Task::create($taskData);
    }

    /**
     * Send email action
     */
    protected function sendEmail(array $action, $model): void
    {
        // This would integrate with Laravel's mail system
        Log::info('Email automation triggered', [
            'to' => $this->replacePlaceholders($action['to'], $model),
            'subject' => $this->replacePlaceholders($action['subject'], $model),
            'template' => $action['template'],
        ]);
    }

    /**
     * Change status action
     */
    protected function changeStatus(array $action, $model): void
    {
        if ($model instanceof Lead) {
            $model->update(['status_id' => $action['status_id']]);
        }
    }

    /**
     * Assign lead action
     */
    protected function assignLead(array $action, $model): void
    {
        if ($model instanceof Lead) {
            $model->update(['assigned_to_user_id' => $action['user_id']]);
        }
    }

    /**
     * Send notification action
     */
    protected function sendNotification(array $action, $model): void
    {
        $user = User::find($action['user_id']);
        if ($user) {
            $this->notificationService->sendNotificationByType(
                $user,
                $action['notification_type'],
                ['model' => $model]
            );
        }
    }

    /**
     * Update score action
     */
    protected function updateScore(array $action, $model): void
    {
        if ($model instanceof Lead) {
            $newScore = $action['score'] ?? $model->score + ($action['increment'] ?? 0);
            $model->update(['score' => $newScore]);
        }
    }

    /**
     * Create interaction action
     */
    protected function createInteraction(array $action, $model): void
    {
        if ($model instanceof Lead) {
            \App\Models\Interaction::create([
                'lead_id' => $model->id,
                'user_id' => $model->assigned_to_user_id ?? $model->created_by_user_id,
                'type' => $action['interaction_type'],
                'summary' => $this->replacePlaceholders($action['summary'], $model),
                'details' => $this->replacePlaceholders($action['details'] ?? '', $model),
                'interaction_date' => now(),
            ]);
        }
    }

    /**
     * Replace placeholders in text
     */
    protected function replacePlaceholders(string $text, $model): string
    {
        $placeholders = [
            '{{lead.name}}' => $model->full_name ?? '',
            '{{lead.email}}' => $model->email ?? '',
            '{{lead.company}}' => $model->company ?? '',
            '{{lead.phone}}' => $model->phone ?? '',
            '{{lead.status}}' => $model->status->name ?? '',
            '{{lead.priority}}' => $model->priority ?? '',
            '{{lead.score}}' => $model->score ?? 0,
            '{{user.name}}' => $model->assignedTo->name ?? '',
            '{{date}}' => now()->format('Y-m-d'),
            '{{time}}' => now()->format('H:i:s'),
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }

    /**
     * Calculate due date from string
     */
    protected function calculateDueDate(string $dueDate): \DateTime
    {
        return now()->modify($dueDate);
    }

    /**
     * Create automation
     */
    public function createAutomation(array $data): Automation
    {
        return Automation::create($data);
    }

    /**
     * Update automation
     */
    public function updateAutomation(Automation $automation, array $data): bool
    {
        return $automation->update($data);
    }

    /**
     * Delete automation
     */
    public function deleteAutomation(Automation $automation): bool
    {
        return $automation->delete();
    }

    /**
     * Test automation
     */
    public function testAutomation(Automation $automation, $model): array
    {
        $results = [];

        foreach ($automation->actions as $action) {
            try {
                $this->executeAction($action, $model);
                $results[] = [
                    'action' => $action['type'],
                    'status' => 'success',
                    'message' => 'Action executed successfully',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'action' => $action['type'],
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
