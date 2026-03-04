<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pacient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pacients';

    protected $fillable = [
        'user_id',
        'centre_id',
        'nom',
        'cognoms',
        'dni',
        'cip',
        'data_naixement',
        'sexe',
        'telefon',
        'email',
        'adreca',
        'poblacio',
        'codi_postal',
        'data_alta',
        'data_baixa',
        'actiu',
        'consentiment_rgpd',
        'data_consentiment',
    ];

    protected function casts(): array
    {
        return [
            'data_naixement' => 'date',
            'data_alta' => 'date',
            'data_baixa' => 'date',
            'actiu' => 'boolean',
            'consentiment_rgpd' => 'boolean',
            'data_consentiment' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    public function participacions()
    {
        return $this->hasMany(Participacio::class);
    }

    // Accessors
    public function getNomCompletAttribute(): string
    {
        return "{$this->nom} {$this->cognoms}";
    }

    public function getEdatAttribute(): ?int
    {
        if ($this->data_naixement) {
            return $this->data_naixement->age;
        }
        return null;
    }

    // Scopes
    public function scopeActiu($query)
    {
        return $query->where('actiu', true);
    }

    public function scopeAmbConsentiment($query)
    {
        return $query->where('consentiment_rgpd', true);
    }
}
