<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function stages()
    {
        return $this->hasMany(Stage::class)->orderBy('order');
    }

    public function leads()
    {
        return $this->hasManyThrough(Lead::class, Stage::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Méthodes utilitaires
    public function getTotalLeadsAttribute()
    {
        return $this->leads()->count();
    }

    public function getConversionRateAttribute()
    {
        $totalLeads = $this->leads()->count();
        if ($totalLeads === 0) return 0;

        $wonLeads = $this->leads()->whereHas('currentStage', function ($query) {
            $query->where('is_final', true)->where('name', 'like', '%Gagné%');
        })->count();

        return round(($wonLeads / $totalLeads) * 100, 2);
    }
}