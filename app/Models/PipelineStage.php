<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id',
        'name',
        'order',
        'color_code',
    ];

    /**
     * Get the pipeline that owns the stage.
     */
    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * Get the leads for the stage.
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'pipeline_stage_id');
    }
}
