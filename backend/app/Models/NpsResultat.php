<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpsResultat extends Model
{
    use HasFactory;

    protected $table = 'nps_resultats';

    protected $fillable = [
        'enquesta_id',
        'participacio_id',
        'centre_id',
        'fisioterapeuta_id',
        'puntuacio',
        'categoria',
        'comentari',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->categoria = self::calcularCategoria($model->puntuacio);
        });

        static::updating(function ($model) {
            $model->categoria = self::calcularCategoria($model->puntuacio);
        });
    }

    public static function calcularCategoria(int $puntuacio): string
    {
        if ($puntuacio >= 9) {
            return 'promotor';
        } elseif ($puntuacio >= 7) {
            return 'passiu';
        } else {
            return 'detractor';
        }
    }

    // Relationships
    public function enquesta()
    {
        return $this->belongsTo(Enquesta::class);
    }

    public function participacio()
    {
        return $this->belongsTo(Participacio::class);
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    public function fisioterapeuta()
    {
        return $this->belongsTo(Fisioterapeuta::class);
    }

    // Scopes
    public function scopePromotors($query)
    {
        return $query->where('categoria', 'promotor');
    }

    public function scopePassius($query)
    {
        return $query->where('categoria', 'passiu');
    }

    public function scopeDetractors($query)
    {
        return $query->where('categoria', 'detractor');
    }

    public function scopeEntreDates($query, $dataInici, $dataFi)
    {
        return $query->whereBetween('data', [$dataInici, $dataFi]);
    }
}
