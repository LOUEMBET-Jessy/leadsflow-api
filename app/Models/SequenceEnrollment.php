<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SequenceEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence_id',
        'lead_id',
        'status',
        'started_at',
        'completed_at',
        'paused_at',
        'current_step',
        'next_send_at',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'next_send_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relations
    public function sequence()
    {
        return $this->belongsTo(EmailSequence::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'active')
                    ->where('next_send_at', '<=', now());
    }

    // Méthodes utilitaires
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPaused()
    {
        return $this->status === 'paused';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isReadyToSend()
    {
        return $this->isActive() && $this->next_send_at && $this->next_send_at <= now();
    }

    public function pause()
    {
        $this->update([
            'status' => 'paused',
            'paused_at' => now(),
        ]);
    }

    public function resume()
    {
        $this->update([
            'status' => 'active',
            'paused_at' => null,
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function advanceToNextStep()
    {
        $nextStep = $this->sequence->steps()
            ->where('order', '>', $this->current_step)
            ->where('is_active', true)
            ->orderBy('order')
            ->first();

        if (!$nextStep) {
            $this->complete();
            return;
        }

        $this->update([
            'current_step' => $nextStep->order,
            'next_send_at' => now()->addDays($nextStep->delay_days),
        ]);
    }

    public function getCurrentStepModel()
    {
        return $this->sequence->steps()
            ->where('order', $this->current_step)
            ->first();
    }

    public function getProgressPercentageAttribute()
    {
        $totalSteps = $this->sequence->steps()->count();
        if ($totalSteps === 0) return 0;

        return round(($this->current_step / $totalSteps) * 100, 2);
    }

    public function getDaysInSequenceAttribute()
    {
        return $this->started_at->diffInDays(now());
    }
}
