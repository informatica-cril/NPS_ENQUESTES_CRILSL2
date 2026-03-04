<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fisioterapeuta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fisioterapeutes';

    protected $fillable = [
        'user_id',
        'centre_id',
        'nom',
        'cognoms',
        'num_colegiat',
        'especialitat',
        'data_alta',
        'actiu',
    ];

    protected function casts(): array
    {
        return [
            'data_alta' => 'date',
            'actiu' => 'boolean',
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

    public function npsResultats()
    {
        return $this->hasMany(NpsResultat::class);
    }

    public function informes()
    {
        return $this->hasMany(Informe::class);
    }

    // Accessors
    public function getNomCompletAttribute(): string
    {
        return "{$this->nom} {$this->cognoms}";
    }

    // Scopes
    public function scopeActiu($query)
    {
        return $query->where('actiu', true);
    }
}
