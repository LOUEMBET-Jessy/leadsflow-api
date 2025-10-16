<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color_code',
        'is_final',
        'order',
    ];

    protected $casts = [
        'is_final' => 'boolean',
    ];

    /**
     * Get the leads for the status.
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'status_id');
    }
}
