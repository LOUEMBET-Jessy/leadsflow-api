<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id',
        'name',
        'description',
        'order',
        'color',
        'is_final',
    ];

    protected $casts = [
        'is_final' => 'boolean',
    ];

    // Relations
    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'current_stage_id');
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    public function scopeNonFinal($query)
    {
        return $query->where('is_final', false);
    }

    // Méthodes utilitaires
    public function getLeadCountAttribute()
    {
        return $this->leads()->count();
    }

    public function getConversionRateAttribute()
    {
        $totalLeads = $this->leads()->count();
        if ($totalLeads === 0) return 0;

        $wonLeads = $this->leads()->where('status', 'Gagné')->count();
        return round(($wonLeads / $totalLeads) * 100, 2);
    }
}
