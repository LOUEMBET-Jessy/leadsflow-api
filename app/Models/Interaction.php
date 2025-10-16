<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'subject',
        'summary',
        'details',
        'date',
        'duration',
        'outcome',
        'metadata',
    ];

    protected $casts = [
        'date' => 'datetime',
        'metadata' => 'array',
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
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByOutcome($query, $outcome)
    {
        return $query->where('outcome', $outcome);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    // Méthodes utilitaires
    public function getTypeIconAttribute()
    {
        return match($this->type) {
            'Email' => '📧',
            'Appel' => '📞',
            'Reunion' => '🤝',
            'Note' => '📝',
            'SMS' => '💬',
            'Chat' => '💭',
            default => '📝'
        };
    }

    public function getOutcomeColorAttribute()
    {
        return match($this->outcome) {
            'positive' => '#27ae60',
            'neutral' => '#f39c12',
            'negative' => '#e74c3c',
            'follow_up_required' => '#3498db',
            default => '#95a5a6'
        };
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) return null;
        
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'min';
        }
        
        return $minutes . 'min';
    }
}