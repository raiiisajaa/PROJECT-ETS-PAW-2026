<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsPenilaianData;
use Tests\TestCase;

class RekapNilaiTest extends TestCase
{
    use BuildsPenilaianData;
    use RefreshDatabase;

    public function test_halaman_rekap_menampilkan_nilai_akhir_grade_dan_status(): void
    {
        [$dosen, $mataKuliah, $kelas, $mahasiswa] = $this->skenario();

        foreach ([80, 90, 75, 85] as $i => $nilai) {
            $mahasiswa->nilais()->create(['komponen_id' => $mataKuliah->komponens[$i]->id, 'nilai' => $nilai]);
        }

        $this->actingAs($dosen)
            ->get(route('rekap.index', ['mata_kuliah' => $mataKuliah->id]))
            ->assertOk()
            ->assertSee($mahasiswa->nim)
            ->assertSee($mahasiswa->nama)
            ->assertSee('82,50')
            ->assertSee('AB')
            ->assertSee('Lulus');
    }

    public function test_pencarian_rekap_berdasarkan_nim_atau_nama(): void
    {
        [$dosen, $mataKuliah, $kelas, $mahasiswa] = $this->skenario();

        $this->actingAs($dosen)
            ->get(route('rekap.index', ['q' => $mahasiswa->nim]))
            ->assertOk()
            ->assertSee($mahasiswa->nim);
    }

    public function test_export_excel_mengembalikan_berkas(): void
    {
        [$dosen] = $this->skenario();

        $this->actingAs($dosen)
            ->get(route('rekap.export'))
            ->assertOk()
            ->assertDownload();
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\MataKuliah, 2: \App\Models\Kelas, 3: \App\Models\Mahasiswa}
     */
    private function skenario(): array
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen);
        $kelas = $this->kelas($mataKuliah);
        $mahasiswa = $this->mahasiswa($kelas);

        return [$dosen, $mataKuliah, $kelas, $mahasiswa];
    }
}
