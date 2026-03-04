<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    use HasFactory;

    protected $table = 'preguntes';

    protected $fillable = [
        'enquesta_id',
        'text_pregunta',
        'descripcio',
        'tipus',
        'ordre',
        'obligatoria',
        'opcions',
        'configuracio',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'obligatoria' => 'boolean',
            'activa' => 'boolean',
            'opcions' => 'array',
            'configuracio' => 'array',
        ];
    }

    // Relationships
    public function enquesta()
    {
        return $this->belongsTo(Enquesta::class);
    }

    public function respostes()
    {
        return $this->hasMany(Resposta::class);
    }

    // Scopes
    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }

    public function scopeOrdenades($query)
    {
        return $query->orderBy('ordre');
    }

    // Helpers
    public function isNps(): bool
    {
        return $this->tipus === 'nps';
    }

    public function getOpcionsList(): array
    {
        return $this->opcions ?? [];
    }
}
