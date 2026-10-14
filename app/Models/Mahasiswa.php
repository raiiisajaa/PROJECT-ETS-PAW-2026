<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'kelas_id',
        'nim',
        'nama',
        'program_studi',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Mata kuliah dari mahasiswa ini (melewati tabel kelas).
     */
    public function mataKuliah(): HasOneThrough
    {
        return $this->hasOneThrough(
            MataKuliah::class,
            Kelas::class,
            'id',            // foreign key di kelas
            'id',            // foreign key di mata_kuliah
            'kelas_id',      // local key di mahasiswa
            'mata_kuliah_id' // local key di kelas
        );
    }

    /**
     * Nilai satu komponen untuk mahasiswa ini (null bila belum dinilai).
     */
    public function nilaiKomponen(int $komponenId): ?float
    {
        $nilai = $this->nilais->firstWhere('komponen_id', $komponenId);

        return $nilai ? (float) $nilai->nilai : null;
    }
}
