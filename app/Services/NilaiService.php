<?php

namespace App\Services;

use App\Models\KomponenPenilaian;
use App\Models\Mahasiswa;
use Illuminate\Support\Collection;

/**
 * NilaiService — satu sumber kebenaran (single source of truth) untuk
 * perhitungan nilai akhir, penentuan grade, dan status kelulusan.
 *
 * Rumus: Nilai Akhir = Σ (Nilai Komponen × Bobot Komponen)
 * - Nilai komponen: 0..100
 * - Bobot: desimal (20% = 0,20); total harus 1,00
 * - Hasil dibulatkan 2 angka di belakang koma
 *
 * Pemetaan grade memakai nilai yang sudah dibulatkan 2 desimal lalu
 * dibandingkan dengan batas bawah tiap rentang (lihat docs/mekanisme-perhitungan-nilai.md).
 * Contoh: 84,50 -> AB ; 85,00 -> A ; 54,75 -> D.
 */
class NilaiService
{
    /** Total bobot yang diwajibkan (1,00 = 100%). */
    public const BOBOT_TOTAL = 1.00;

    /** Toleransi pembulatan saat membandingkan total bobot. */
    public const TOLERANSI_BOBOT = 0.001;

    /**
     * Tabel grade: diurutkan menurun berdasarkan batas bawah.
     * Nilai dinyatakan dalam skala 0..100.
     *
     * @var array<int, array{min: float, grade: string, status: string}>
     */
    public const GRADE_RANGES = [
        ['min' => 85.0, 'max' => 100.0, 'grade' => 'A',  'status' => 'Lulus'],
        ['min' => 80.0, 'max' => 84.99, 'grade' => 'AB', 'status' => 'Lulus'],
        ['min' => 70.0, 'max' => 79.99, 'grade' => 'B',  'status' => 'Lulus'],
        ['min' => 65.0, 'max' => 69.99, 'grade' => 'BC', 'status' => 'Lulus'],
        ['min' => 55.0, 'max' => 64.99, 'grade' => 'C',  'status' => 'Lulus'],
        ['min' => 40.0, 'max' => 54.99, 'grade' => 'D',  'status' => 'Tidak Lulus'],
        ['min' => 0.0,  'max' => 39.99, 'grade' => 'E',  'status' => 'Tidak Lulus'],
    ];

    /**
     * Daftar grade yang dinyatakan Lulus.
     *
     * @var array<int, string>
     */
    public const GRADE_LULUS = ['A', 'AB', 'B', 'BC', 'C'];

    /**
     * Hitung nilai akhir seorang mahasiswa.
     *
     * @param  Collection<int, KomponenPenilaian>|null  $komponens
     */
    public function hitungNilaiAkhir(Mahasiswa $mahasiswa, ?Collection $komponens = null): float
    {
        $komponens ??= $this->komponens($mahasiswa);

        $mahasiswa->loadMissing('nilais');

        $total = 0.0;
        foreach ($komponens as $komponen) {
            $nilai = $mahasiswa->nilaiKomponen($komponen->id) ?? 0.0;
            $total += $nilai * (float) $komponen->bobot;
        }

        return round($total, 2);
    }

    /**
     * Tentukan grade dari nilai akhir.
     */
    public function tentukanGrade(float $nilaiAkhir): string
    {
        $nilai = round($nilaiAkhir, 2);

        foreach (self::GRADE_RANGES as $range) {
            if ($nilai >= $range['min']) {
                return $range['grade'];
            }
        }

        return 'E';
    }

    /**
     * Tentukan status kelulusan dari nilai akhir.
     */
    public function tentukanStatus(float $nilaiAkhir): string
    {
        return $this->statusUntukGrade($this->tentukanGrade($nilaiAkhir));
    }

    /**
     * Status kelulusan dari sebuah grade.
     */
    public function statusUntukGrade(string $grade): string
    {
        return in_array($grade, self::GRADE_LULUS, true) ? 'Lulus' : 'Tidak Lulus';
    }

    /**
     * Ringkasan lengkap: rincian perhitungan per komponen + nilai akhir, grade, status.
     *
     * @param  Collection<int, KomponenPenilaian>|null  $komponens
     * @return array{
     *     nilai_akhir: float,
     *     grade: string,
     *     status: string,
     *     lengkap: bool,
     *     detail: array<int, array{komponen: string, nilai: float|null, bobot: float, bobot_persen: float, kontribusi: float}>
     * }
     */
    public function ringkasan(Mahasiswa $mahasiswa, ?Collection $komponens = null): array
    {
        $komponens ??= $this->komponens($mahasiswa);
        $mahasiswa->loadMissing('nilais');

        $detail = [];
        $total = 0.0;
        $lengkap = true;

        foreach ($komponens as $komponen) {
            $nilai = $mahasiswa->nilaiKomponen($komponen->id);
            $bobot = (float) $komponen->bobot;
            $kontribusi = round(($nilai ?? 0.0) * $bobot, 2);

            if ($nilai === null) {
                $lengkap = false;
            }

            $detail[] = [
                'komponen' => $komponen->nama,
                'nilai' => $nilai,
                'bobot' => $bobot,
                'bobot_persen' => round($bobot * 100, 2),
                'kontribusi' => $kontribusi,
            ];

            $total += $kontribusi;
        }

        $nilaiAkhir = round($total, 2);
        $grade = $this->tentukanGrade($nilaiAkhir);

        return [
            'nilai_akhir' => $nilaiAkhir,
            'grade' => $grade,
            'status' => $this->statusUntukGrade($grade),
            'lengkap' => $lengkap,
            'detail' => $detail,
        ];
    }

    /**
     * Validasi total bobot tepat 100% (dengan toleransi kecil).
     *
     * @param  iterable<KomponenPenilaian|array<string, mixed>>  $komponens
     */
    public function totalBobotValid(iterable $komponens): bool
    {
        $total = 0.0;
        foreach ($komponens as $komponen) {
            $total += (float) (is_array($komponen) ? ($komponen['bobot'] ?? 0) : $komponen->bobot);
        }

        return abs($total - self::BOBOT_TOTAL) < self::TOLERANSI_BOBOT;
    }

    /**
     * Ambil komponen dari mata kuliah milik mahasiswa.
     *
     * @return Collection<int, KomponenPenilaian>
     */
    protected function komponens(Mahasiswa $mahasiswa): Collection
    {
        $mahasiswa->loadMissing('kelas.mataKuliah.komponens');

        return $mahasiswa->kelas->mataKuliah->komponens->sortBy('id')->values();
    }
}
