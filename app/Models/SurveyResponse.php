<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    protected $table = 'survey_responses';

    protected $fillable = [
        'patient_id',
        'nps_score',
        'service_quality',
        'punctuality',
        'treatment',
        'perceived_improvement',
        'communication',
        'global_experience',
        'duration_adequate',
        'session_over_30_min',
        'comments',
    ];

    protected $casts = [
        'duration_adequate' => 'boolean',
        'session_over_30_min' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Calcular categoría NPS según puntuación
     */
    public function getNpsCategory(): string
    {
        return match(true) {
            $this->nps_score >= 9 => 'Promotor',
            $this->nps_score >= 7 => 'Pasiu',
            default => 'Detractor'
        };
    }
}
