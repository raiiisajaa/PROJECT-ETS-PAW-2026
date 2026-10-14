<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'user_id',
        'kode',
        'nama',
        'semester',
        'sks',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function komponens(): HasMany
    {
        return $this->hasMany(KomponenPenilaian::class)->orderBy('id');
    }

    public function mahasiswas(): HasManyThrough
    {
        return $this->hasManyThrough(Mahasiswa::class, Kelas::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Total bobot seluruh komponen mata kuliah ini (desimal, 1.00 = 100%).
     */
    public function totalBobot(): float
    {
        return (float) $this->komponens()->sum('bobot');
    }
}
