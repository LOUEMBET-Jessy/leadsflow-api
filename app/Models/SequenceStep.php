<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SequenceStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence_id',
        'order',
        'delay_days',
        'subject',
        'email_template',
        'text_template',
        'personalization_tags',
        'is_active',
    ];

    protected $casts = [
        'personalization_tags' => 'array',
        'is_active' => 'boolean',
    ];

    // Relations
    public function sequence()
    {
        return $this->belongsTo(EmailSequence::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Méthodes utilitaires
    public function personalizeContent($lead)
    {
        $content = $this->email_template;
        $tags = $this->personalization_tags ?? [];

        foreach ($tags as $tag) {
            $value = $this->getPersonalizationValue($lead, $tag);
            $content = str_replace('{{' . $tag . '}}', $value, $content);
        }

        return $content;
    }

    protected function getPersonalizationValue($lead, $tag)
    {
        return match($tag) {
            'first_name' => explode(' ', $lead->name)[0] ?? $lead->name,
            'last_name' => explode(' ', $lead->name)[1] ?? '',
            'full_name' => $lead->name,
            'email' => $lead->email,
            'company' => $lead->company ?? '',
            'phone' => $lead->phone ?? '',
            'location' => $lead->location ?? '',
            'source' => $lead->source ?? '',
            'score' => $lead->score,
            'status' => $lead->status,
            'stage_name' => $lead->currentStage?->name ?? '',
            'pipeline_name' => $lead->currentStage?->pipeline?->name ?? '',
            default => ''
        };
    }

    public function getPersonalizedSubject($lead)
    {
        $subject = $this->subject;
        $tags = $this->personalization_tags ?? [];

        foreach ($tags as $tag) {
            $value = $this->getPersonalizationValue($lead, $tag);
            $subject = str_replace('{{' . $tag . '}}', $value, $subject);
        }

        return $subject;
    }

    public function getPersonalizedTextTemplate($lead)
    {
        $content = $this->text_template ?: strip_tags($this->email_template);
        $tags = $this->personalization_tags ?? [];

        foreach ($tags as $tag) {
            $value = $this->getPersonalizationValue($lead, $tag);
            $content = str_replace('{{' . $tag . '}}', $value, $content);
        }

        return $content;
    }

    public function getDelayHoursAttribute()
    {
        return $this->delay_days * 24;
    }

    public function getIsFirstStepAttribute()
    {
        return $this->order === 1;
    }

    public function getIsLastStepAttribute()
    {
        return $this->order === $this->sequence->steps()->max('order');
    }
}
