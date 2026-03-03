<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Participacio extends Model
{
    use HasFactory;

    protected $table = 'participacions';

    protected $fillable = [
        'enquesta_id',
        'pacient_id',
        'fisioterapeuta_id',
        'token',
        'estat',
        'data_inici',
        'data_completat',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'data_inici' => 'datetime',
            'data_completat' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::uuid()->toString();
            }
        });
    }

    // Relationships
    public function enquesta()
    {
        return $this->belongsTo(Enquesta::class);
    }

    public function pacient()
    {
        return $this->belongsTo(Pacient::class);
    }

    public function fisioterapeuta()
    {
        return $this->belongsTo(Fisioterapeuta::class);
    }

    public function respostes()
    {
        return $this->hasMany(Resposta::class);
    }

    public function npsResultat()
    {
        return $this->hasOne(NpsResultat::class);
    }

    // Scopes
    public function scopeCompletada($query)
    {
        return $query->where('estat', 'completada');
    }

    public function scopePendent($query)
    {
        return $query->where('estat', 'pendent');
    }

    // Helpers
    public function completar()
    {
        $this->update([
            'estat' => 'completada',
            'data_completat' => now(),
        ]);
    }

    public function iniciar()
    {
        if ($this->estat === 'pendent') {
            $this->update([
                'estat' => 'en_curs',
                'data_inici' => now(),
            ]);
        }
    }
}
