<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Physiotherapist extends Model
{
    use HasApiTokens;
{
    protected $table = 'physiotherapists';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'territory',
        'is_active',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'physiotherapist_id');
    }
}
