<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Fitxer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fitxers';

    protected $fillable = [
        'nom_original',
        'nom_emmagatzemat',
        'path',
        'disk',
        'mime_type',
        'mida',
        'fitxerable_type',
        'fitxerable_id',
        'uploaded_by',
    ];

    // Relationships
    public function fitxerable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helpers
    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getTemporaryUrl(int $minutes = 60): string
    {
        return Storage::disk($this->disk)->temporaryUrl(
            $this->path,
            now()->addMinutes($minutes)
        );
    }

    public function getMidaFormatedAttribute(): string
    {
        $bytes = $this->mida;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function delete()
    {
        Storage::disk($this->disk)->delete($this->path);
        return parent::delete();
    }
}
