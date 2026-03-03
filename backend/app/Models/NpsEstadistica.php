<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpsEstadistica extends Model
{
    use HasFactory;

    protected $table = 'nps_estadistiques';

    protected $fillable = [
        'enquesta_id',
        'centre_id',
        'fisioterapeuta_id',
        'periode',
        'data_inici',
        'data_fi',
        'total_respostes',
        'promotors',
        'passius',
        'detractors',
        'nps_score',
        'mitjana_puntuacio',
    ];

    protected function casts(): array
    {
        return [
            'data_inici' => 'date',
            'data_fi' => 'date',
            'nps_score' => 'decimal:2',
            'mitjana_puntuacio' => 'decimal:2',
        ];
    }

    // Relationships
    public function enquesta()
    {
        return $this->belongsTo(Enquesta::class);
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    public function fisioterapeuta()
    {
        return $this->belongsTo(Fisioterapeuta::class);
    }

    // Calculate NPS score
    public function calcularNps()
    {
        if ($this->total_respostes > 0) {
            $percentPromotors = ($this->promotors / $this->total_respostes) * 100;
            $percentDetractors = ($this->detractors / $this->total_respostes) * 100;
            $this->nps_score = $percentPromotors - $percentDetractors;
        } else {
            $this->nps_score = null;
        }
        return $this;
    }
}
