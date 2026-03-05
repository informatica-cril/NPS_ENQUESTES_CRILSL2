<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function fisioterapeuta()
    {
        return $this->hasOne(Fisioterapeuta::class);
    }

    public function pacient()
    {
        return $this->hasOne(Pacient::class);
    }

    public function enquestesCreades()
    {
        return $this->hasMany(Enquesta::class, 'created_by');
    }

    public function informes()
    {
        return $this->hasMany(Informe::class, 'created_by');
    }

    // Helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isFisioterapeuta(): bool
    {
        return $this->role === 'fisioterapeuta';
    }

    public function isPacient(): bool
    {
        return $this->role === 'pacient';
    }
}
