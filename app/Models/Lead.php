<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'current_stage_id',
        'name',
        'email',
        'phone',
        'company',
        'status',
        'source',
        'location',
        'score',
        'estimated_value',
        'notes',
        'custom_fields',
        'last_contact_at',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'last_contact_at' => 'datetime',
        'estimated_value' => 'decimal:2',
    ];

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currentStage()
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'lead_assignments')
                    ->withPivot('assigned_at', 'assigned_by_user_id', 'notes')
                    ->withTimestamps();
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class)->orderBy('date', 'desc');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('due_date');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByStage($query, $stageId)
    {
        return $query->where('current_stage_id', $stageId);
    }

    public function scopeHighScore($query, $minScore = 80)
    {
        return $query->where('score', '>=', $minScore);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereHas('assignedUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    // Méthodes utilitaires
    public function isHot()
    {
        return $this->status === 'Chaud' || $this->score >= 80;
    }

    public function isCold()
    {
        return $this->status === 'Froid' || $this->score <= 30;
    }

    public function isWon()
    {
        return $this->status === 'Gagné';
    }

    public function isLost()
    {
        return $this->status === 'Perdu';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Nouveau' => '#3498db',
            'Contacté' => '#f39c12',
            'Qualification' => '#e67e22',
            'Négociation' => '#e74c3c',
            'Gagné' => '#27ae60',
            'Perdu' => '#95a5a6',
            'Chaud' => '#e74c3c',
            'Froid' => '#3498db',
            'A_recontacter' => '#f39c12',
            'Non_qualifié' => '#95a5a6',
            default => '#95a5a6'
        };
    }

    public function getLastInteractionAttribute()
    {
        return $this->interactions()->first();
    }

    public function getOpenTasksCountAttribute()
    {
        return $this->tasks()->whereIn('status', ['EnCours', 'Retard'])->count();
    }

    public function getOverdueTasksCountAttribute()
    {
        return $this->tasks()->where('status', 'Retard')->count();
    }

    public function getDaysSinceLastContactAttribute()
    {
        if (!$this->last_contact_at) {
            return $this->created_at->diffInDays(now());
        }
        return $this->last_contact_at->diffInDays(now());
    }
}