<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'plan',
        'settings',
        'is_active',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    // Relations
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function pipelines()
    {
        return $this->hasMany(Pipeline::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function automationRules()
    {
        return $this->hasMany(AutomationRule::class);
    }

    public function emailSequences()
    {
        return $this->hasMany(EmailSequence::class);
    }

    public function segments()
    {
        return $this->hasMany(Segment::class);
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Méthodes utilitaires
    public function isTrialActive()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isSubscriptionActive()
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }
}
