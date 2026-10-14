<?php

namespace Database\Seeders;

use App\Models\MataKuliah;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed data awal: dosen, mata kuliah, kelas, mahasiswa, komponen, nilai, feedback.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // ---- Dosen ----
        $dosen = User::updateOrCreate(
            ['email' => 'raiiisaja@gmail.com'],
            [
                'name' => 'Dzaky Raihan Fauzan',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Bersihkan data mata kuliah dosen agar seeder dapat dijalankan ulang.
        $dosen->mataKuliahs()->delete();

        // ---- Mata kuliah ----
        $daftarMk = [
            ['kode' => 'IF2101', 'nama' => 'Pemrograman Aplikasi Web', 'semester' => 5, 'sks' => 3],
            ['kode' => 'IF2102', 'nama' => 'Basis Data', 'semester' => 3, 'sks' => 3],
            ['kode' => 'IF2103', 'nama' => 'Algoritma dan Struktur Data', 'semester' => 3, 'sks' => 4],
        ];

        // Komponen standar: total bobot tepat 100% (sesuai kriteria penerimaan PRD).
        $komponenStandar = [
            ['nama' => 'Tugas 1', 'bobot' => 0.20],
            ['nama' => 'Tugas 2', 'bobot' => 0.20],
            ['nama' => 'UTS', 'bobot' => 0.25],
            ['nama' => 'UAS', 'bobot' => 0.35],
        ];

        foreach ($daftarMk as $mkIndex => $data) {
            /** @var MataKuliah $mataKuliah */
            $mataKuliah = $dosen->mataKuliahs()->create($data);

            foreach ($komponenStandar as $komponen) {
                $mataKuliah->komponens()->create($komponen);
            }

            $mataKuliah->load('komponens');

            foreach (['A', 'B'] as $kelasIndex => $huruf) {
                $kelas = $mataKuliah->kelas()->create(['nama_kelas' => 'Kelas '.$huruf]);

                for ($i = 1; $i <= 10; $i++) {
                    $nim = sprintf('2311%02d%d%03d', $mkIndex + 1, $kelasIndex + 1, $i);

                    $mahasiswa = $kelas->mahasiswas()->create([
                        'nim' => $nim,
                        'nama' => $faker->name(),
                        'program_studi' => 'Teknik Informatika',
                    ]);

                    foreach ($mataKuliah->komponens as $komponen) {
                        // Contoh nilai khusus untuk dokumentasi (PRD: 82,50 -> AB).
                        $khusus = $mkIndex === 0 && $kelasIndex === 0 && $i === 1;

                        $nilai = match (true) {
                            $khusus && $komponen->nama === 'Tugas 1' => 80,
                            $khusus && $komponen->nama === 'Tugas 2' => 90,
                            $khusus && $komponen->nama === 'UTS' => 75,
                            $khusus && $komponen->nama === 'UAS' => 85,
                            default => $faker->numberBetween(55, 98),
                        };

                        $mahasiswa->nilais()->create([
                            'komponen_id' => $komponen->id,
                            'nilai' => $nilai,
                        ]);
                    }
                }
            }
        }

        // ---- Feedback contoh ----
        $mataKuliahPertama = $dosen->mataKuliahs()->first();
        $mahasiswaPertama = $mataKuliahPertama->mahasiswas()->first();

        if ($mahasiswaPertama) {
            $mahasiswaPertama->feedbacks()->create([
                'mata_kuliah_id' => $mataKuliahPertama->id,
                'isi' => 'Pemahaman konsep sudah baik. Pertahankan dan tingkatkan lagi latihan pada bagian UAS.',
            ]);
        }
    }
}
