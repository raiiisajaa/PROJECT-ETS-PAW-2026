<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilai';

    protected $fillable = [
        'mahasiswa_id',
        'komponen_id',
        'nilai',
    ];

    protected $casts = [
        'nilai' => 'float',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(KomponenPenilaian::class, 'komponen_id');
    }
}
