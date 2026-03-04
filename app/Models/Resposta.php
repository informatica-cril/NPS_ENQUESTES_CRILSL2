<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resposta extends Model
{
    use HasFactory;

    protected $table = 'respostes';

    protected $fillable = [
        'participacio_id',
        'pregunta_id',
        'valor_text',
        'valor_numeric',
        'valor_json',
    ];

    protected function casts(): array
    {
        return [
            'valor_json' => 'array',
        ];
    }

    // Relationships
    public function participacio()
    {
        return $this->belongsTo(Participacio::class);
    }

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class);
    }

    // Helpers
    public function getValorAttribute()
    {
        if ($this->valor_numeric !== null) {
            return $this->valor_numeric;
        }
        if ($this->valor_json !== null) {
            return $this->valor_json;
        }
        return $this->valor_text;
    }
}
