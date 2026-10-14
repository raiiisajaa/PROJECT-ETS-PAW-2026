<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Impor nilai dari Excel.
 *
 * Format berkas (baris pertama = heading):
 * | nim | nama (opsional) | <nama komponen 1> | <nama komponen 2> | ... |
 *
 * - NIM harus terdaftar pada kelas tujuan.
 * - Nilai tiap komponen harus angka 0..100 (FR-20, FR-21).
 * - Baris bermasalah dikumpulkan pada $gagal tanpa membatalkan baris lain (FR-22).
 */
class NilaiImport implements ToCollection, WithHeadingRow
{
    /** @var array<int, array{mahasiswa_id: int, komponen_id: int, nilai: float}> */
    public array $berhasil = [];

    /** @var array<int, array{baris: int, pesan: string}> */
    public array $gagal = [];

    public function __construct(
        protected MataKuliah $mataKuliah,
        protected int $kelasId
    ) {
    }

    public function collection(Collection $rows): void
    {
        $komponens = $this->mataKuliah->komponens->keyBy(
            fn ($k) => Str::slug($k->nama, '_')
        );

        $mahasiswas = Mahasiswa::where('kelas_id', $this->kelasId)
            ->get()
            ->keyBy(fn ($m) => (string) $m->nim);

        foreach ($rows as $index => $row) {
            $nomorBaris = $index + 2; // baris 1 adalah heading

            // Laravel Excel menyerahkan tiap baris sebagai Collection/Row.
            $data = method_exists($row, 'toArray') ? $row->toArray() : (array) $row;

            $nim = trim((string) ($data['nim'] ?? ''));
            if ($nim === '') {
                continue; // lewati baris kosong
            }

            $mahasiswa = $mahasiswas->get($nim);

            if (! $mahasiswa) {
                $this->gagal[] = [
                    'baris' => $nomorBaris,
                    'pesan' => "NIM \"{$nim}\" tidak terdaftar pada kelas ini.",
                ];
                continue;
            }

            // Validasi seluruh sel komponen pada baris ini terlebih dahulu.
            // Bila salah satu sel bermasalah, seluruh baris dilewati (FR-22).
            $barisNilai = [];
            $pesanGalat = null;

            foreach ($komponens as $slug => $komponen) {
                if (! array_key_exists($slug, $data)) {
                    continue;
                }

                $raw = $data[$slug];

                if ($raw === null || $raw === '') {
                    continue; // sel kosong: dilewati, nilai lama tidak diubah
                }

                if (! is_numeric($raw)) {
                    $pesanGalat ??= "Nilai komponen \"{$komponen->nama}\" untuk NIM {$nim} bukan angka.";
                    continue;
                }

                $nilai = (float) $raw;

                if ($nilai < 0 || $nilai > 100) {
                    $pesanGalat ??= "Nilai komponen \"{$komponen->nama}\" untuk NIM {$nim} di luar rentang 0-100.";
                    continue;
                }

                $barisNilai[] = [
                    'mahasiswa_id' => $mahasiswa->id,
                    'komponen_id' => $komponen->id,
                    'nilai' => round($nilai, 2),
                ];
            }

            if ($pesanGalat !== null) {
                $this->gagal[] = ['baris' => $nomorBaris, 'pesan' => $pesanGalat];

                continue;
            }

            foreach ($barisNilai as $baris) {
                $this->berhasil[] = $baris;
            }
        }
    }
}
