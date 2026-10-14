<?php

namespace Tests\Support;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;

trait BuildsPenilaianData
{
    protected function dosen(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * @param  array<int, array{nama: string, bobot: float}>|null  $komponen
     */
    protected function mataKuliah(User $dosen, ?array $komponen = null): MataKuliah
    {
        $komponen ??= [
            ['nama' => 'Tugas 1', 'bobot' => 0.20],
            ['nama' => 'Tugas 2', 'bobot' => 0.20],
            ['nama' => 'UTS', 'bobot' => 0.25],
            ['nama' => 'UAS', 'bobot' => 0.35],
        ];

        $mataKuliah = MataKuliah::factory()->create(['user_id' => $dosen->id]);

        foreach ($komponen as $row) {
            $mataKuliah->komponens()->create($row);
        }

        return $mataKuliah->fresh('komponens');
    }

    protected function kelas(MataKuliah $mataKuliah, string $nama = 'Kelas A'): Kelas
    {
        return $mataKuliah->kelas()->create(['nama_kelas' => $nama]);
    }

    protected function mahasiswa(Kelas $kelas, array $attributes = []): Mahasiswa
    {
        return Mahasiswa::factory()->create(array_merge(['kelas_id' => $kelas->id], $attributes));
    }
}
