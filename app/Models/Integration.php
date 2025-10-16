<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_name',
        'access_token',
        'refresh_token',
        'expires_at',
        'status',
        'settings',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Get the user for the integration.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include integrations by service.
     */
    public function scopeByService($query, $serviceName)
    {
        return $query->where('service_name', $serviceName);
    }

    /**
     * Scope a query to only include active integrations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'connected');
    }

    /**
     * Check if the integration is expired.
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the integration is active.
     */
    public function isActive()
    {
        return $this->status === 'connected' && !$this->isExpired();
    }
}
