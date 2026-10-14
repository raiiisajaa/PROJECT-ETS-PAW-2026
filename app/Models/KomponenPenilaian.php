<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KomponenPenilaian extends Model
{
    use HasFactory;

    protected $table = 'komponen_penilaian';

    protected $fillable = [
        'mata_kuliah_id',
        'nama',
        'bobot',
    ];

    protected $casts = [
        'bobot' => 'float',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(Nilai::class, 'komponen_id');
    }

    /**
     * Bobot dalam persen bulat untuk tampilan (0.20 -> 20).
     */
    public function getBobotPersenAttribute(): float
    {
        return round($this->bobot * 100, 2);
    }
}
