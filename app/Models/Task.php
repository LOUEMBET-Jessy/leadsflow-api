<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'completed_at',
        'completion_notes',
        'reminders',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'reminders' => 'array',
    ];

    // Relations
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('status', ['EnCours', 'Retard']);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today())
                    ->whereIn('status', ['EnCours', 'Retard']);
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->whereIn('status', ['EnCours', 'Retard']);
    }

    // Méthodes utilitaires
    public function isOverdue()
    {
        return $this->due_date < now() && in_array($this->status, ['EnCours', 'Retard']);
    }

    public function isCompleted()
    {
        return $this->status === 'Complete';
    }

    public function isCancelled()
    {
        return $this->status === 'Cancelled';
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'low' => '#27ae60',
            'medium' => '#f39c12',
            'high' => '#e67e22',
            'urgent' => '#e74c3c',
            default => '#95a5a6'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'EnCours' => '#3498db',
            'Retard' => '#e74c3c',
            'Complete' => '#27ae60',
            'Cancelled' => '#95a5a6',
            default => '#95a5a6'
        };
    }

    public function getDaysUntilDueAttribute()
    {
        return $this->due_date->diffInDays(now(), false);
    }

    public function getIsUrgentAttribute()
    {
        return $this->priority === 'urgent' || $this->isOverdue();
    }
}