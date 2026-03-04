<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Centre extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'postal_code',
        'province',
        'latitude',
        'longitude',
        'phone',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function fisioterapeutes()
    {
        return $this->hasMany(Fisioterapeuta::class);
    }

    public function pacients()
    {
        return $this->hasMany(Pacient::class);
    }

    public function enquestes()
    {
        return $this->hasMany(Enquesta::class);
    }

    public function npsResultats()
    {
        return $this->hasMany(NpsResultat::class);
    }

    public function npsEstadistiques()
    {
        return $this->hasMany(NpsEstadistica::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function getCoordinatesAttribute(): ?array
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }
        return null;
    }
}
