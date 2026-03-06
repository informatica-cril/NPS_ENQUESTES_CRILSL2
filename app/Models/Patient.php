<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $table = 'patients';

    protected $fillable = [
        'full_name',
        'email',
        'cip',
        'dni',
        'territory',
        'treatment_end_date',
        'survey_completed',
        'physiotherapist_id',
    ];

    protected $casts = [
        'survey_completed' => 'boolean',
        'treatment_end_date' => 'date',
    ];

    public function physiotherapist(): BelongsTo
    {
        return $this->belongsTo(Physiotherapist::class, 'physiotherapist_id');
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class, 'patient_id');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'patient_id');
    }
}
