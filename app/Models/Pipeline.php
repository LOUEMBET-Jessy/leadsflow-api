<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_default',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user who created the pipeline.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the stages for the pipeline.
     */
    public function stages()
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order');
    }

    /**
     * Get the leads for the pipeline.
     */
    public function leads()
    {
        return $this->hasManyThrough(Lead::class, PipelineStage::class);
    }
}
