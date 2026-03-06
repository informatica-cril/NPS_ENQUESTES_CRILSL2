<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Missatge extends Model
{
    protected $fillable = ['fisioterapeuta_id','emissor_user_id','emissor_rol','contingut','llegit','llegit_at'];
    protected $casts = ['llegit'=>'boolean','llegit_at'=>'datetime'];
    public function fisioterapeuta(): BelongsTo { return $this->belongsTo(Fisioterapeuta::class); }
    public function emissor(): BelongsTo { return $this->belongsTo(User::class, 'emissor_user_id'); }
}
