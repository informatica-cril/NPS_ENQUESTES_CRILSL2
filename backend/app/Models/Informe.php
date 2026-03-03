<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Informe extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'informes';

    protected $fillable = [
        'titol',
        'descripcio',
        'tipus',
        'enquesta_id',
        'centre_id',
        'fisioterapeuta_id',
        'created_by',
        'data_inici',
        'data_fi',
        'filtres',
        'dades',
        'fitxer_path',
        'estat',
    ];

    protected function casts(): array
    {
        return [
            'data_inici' => 'date',
            'data_fi' => 'date',
            'filtres' => 'array',
            'dades' => 'array',
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

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeCompletats($query)
    {
        return $query->where('estat', 'completat');
    }
}
