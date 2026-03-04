<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Enquesta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'enquestes';

    protected $fillable = [
        'titol',
        'slug',
        'descripcio',
        'tipus',
        'estat',
        'centre_id',
        'created_by',
        'data_inici',
        'data_fi',
        'anonima',
        'requereix_autenticacio',
        'temps_estimat_minuts',
        'imatge_capçalera',
        'configuracio',
    ];

    protected function casts(): array
    {
        return [
            'data_inici' => 'date',
            'data_fi' => 'date',
            'anonima' => 'boolean',
            'requereix_autenticacio' => 'boolean',
            'configuracio' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->titol) . '-' . Str::random(6);
            }
        });
    }

    // Relationships
    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function preguntes()
    {
        return $this->hasMany(Pregunta::class)->orderBy('ordre');
    }

    public function participacions()
    {
        return $this->hasMany(Participacio::class);
    }

    public function npsResultats()
    {
        return $this->hasMany(NpsResultat::class);
    }

    public function npsEstadistiques()
    {
        return $this->hasMany(NpsEstadistica::class);
    }

    public function informes()
    {
        return $this->hasMany(Informe::class);
    }

    // Scopes
    public function scopeActiva($query)
    {
        return $query->where('estat', 'activa');
    }

    public function scopeEnCurs($query)
    {
        $today = now()->toDateString();
        return $query->where('estat', 'activa')
            ->where(function ($q) use ($today) {
                $q->whereNull('data_inici')
                    ->orWhere('data_inici', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('data_fi')
                    ->orWhere('data_fi', '>=', $today);
            });
    }

    // Helpers
    public function isActiva(): bool
    {
        if ($this->estat !== 'activa') {
            return false;
        }

        $today = now()->toDateString();

        if ($this->data_inici && $this->data_inici > $today) {
            return false;
        }

        if ($this->data_fi && $this->data_fi < $today) {
            return false;
        }

        return true;
    }

    public function getTotalParticipacionsAttribute(): int
    {
        return $this->participacions()->where('estat', 'completada')->count();
    }
}
