<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'secret_key',
        'events',
        'is_active',
        'created_by_user_id',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created the webhook endpoint.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Scope a query to only include active webhook endpoints.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include webhook endpoints by event.
     */
    public function scopeByEvent($query, $event)
    {
        return $query->whereJsonContains('events', $event);
    }
}
