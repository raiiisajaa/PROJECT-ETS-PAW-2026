<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;
use App\Services\NilaiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsPenilaianData;
use Tests\TestCase;

class InputNilaiTest extends TestCase
{
    use BuildsPenilaianData;
    use RefreshDatabase;

    private User $dosen;

    private MataKuliah $mataKuliah;

    private Kelas $kelas;

    private Mahasiswa $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dosen = $this->dosen();
        $this->mataKuliah = $this->mataKuliah($this->dosen);
        $this->kelas = $this->kelas($this->mataKuliah);
        $this->mahasiswa = $this->mahasiswa($this->kelas);
    }

    /**
     * Skenario 1: nilai 80 diterima.
     */
    public function test_nilai_80_diterima(): void
    {
        $this->simpan([80, 80, 80, 80])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('nilai', 4);
        $this->assertDatabaseHas('nilai', [
            'mahasiswa_id' => $this->mahasiswa->id,
            'komponen_id' => $this->mataKuliah->komponens[0]->id,
            'nilai' => 80,
        ]);
    }

    /**
     * Skenario 2: nilai 100 diterima.
     */
    public function test_nilai_100_diterima(): void
    {
        $this->simpan([100, 100, 100, 100])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('nilai', 4);
    }

    /**
     * Skenario 3: nilai 101 ditolak.
     */
    public function test_nilai_101_ditolak(): void
    {
        $this->simpan([101, 80, 80, 80])->assertSessionHasErrors('nilai.*.*');

        $this->assertDatabaseCount('nilai', 0);
    }

    /**
     * Skenario 4: nilai -1 ditolak.
     */
    public function test_nilai_minus_satu_ditolak(): void
    {
        $this->simpan([-1, 80, 80, 80])->assertSessionHasErrors('nilai.*.*');

        $this->assertDatabaseCount('nilai', 0);
    }

    /**
     * Skenario 11: nilai bukan angka ditolak.
     */
    public function test_nilai_bukan_angka_ditolak(): void
    {
        $payload = $this->payload([80, 80, 80, 80]);
        $payload['nilai'][$this->mahasiswa->id][$this->mataKuliah->komponens[2]->id] = 'sembilan puluh';

        $this->actingAs($this->dosen)
            ->from(route('nilai.index', $this->mataKuliah))
            ->post(route('nilai.simpan', $this->mataKuliah), $payload)
            ->assertSessionHasErrors('nilai.*.*');

        $this->assertDatabaseCount('nilai', 0);
    }

    /**
     * Skenario 12: validasi browser dinonaktifkan — Laravel tetap memvalidasi.
     */
    public function test_laravel_tetap_memvalidasi_walau_validasi_browser_dilewati(): void
    {
        // Permintaan HTTP langsung ke server, tanpa melewati JavaScript browser.
        $this->simpan([150, 150, 150, 150])->assertSessionHasErrors('nilai.*.*');

        $this->assertDatabaseCount('nilai', 0);
    }

    /**
     * Skenario 8: nilai akhir 85 menghasilkan grade A.
     */
    public function test_nilai_akhir_85_menghasilkan_grade_a(): void
    {
        $this->simpan([85, 85, 85, 85])->assertSessionHasNoErrors();

        $ringkasan = app(NilaiService::class)->ringkasan($this->mahasiswa->fresh('nilais'), $this->mataKuliah->komponens);

        $this->assertSame(85.0, $ringkasan['nilai_akhir']);
        $this->assertSame('A', $ringkasan['grade']);
        $this->assertSame('Lulus', $ringkasan['status']);
    }

    /**
     * Skenario 9: nilai akhir 49 menghasilkan grade D (tidak lulus).
     */
    public function test_nilai_akhir_49_menghasilkan_grade_d(): void
    {
        $this->simpan([49, 49, 49, 49])->assertSessionHasNoErrors();

        $ringkasan = app(NilaiService::class)->ringkasan($this->mahasiswa->fresh('nilais'), $this->mataKuliah->komponens);

        $this->assertSame(49.0, $ringkasan['nilai_akhir']);
        $this->assertSame('D', $ringkasan['grade']);
        $this->assertSame('Tidak Lulus', $ringkasan['status']);
    }

    public function test_nilai_kosong_menghapus_nilai_yang_tersimpan(): void
    {
        $this->simpan([80, 80, 80, 80]);
        $this->assertDatabaseCount('nilai', 4);

        $payload = $this->payload(['', '', '', '']);
        $this->actingAs($this->dosen)
            ->post(route('nilai.simpan', $this->mataKuliah), $payload)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('nilai', 0);
    }

    private function simpan(array $nilai)
    {
        return $this->actingAs($this->dosen)
            ->from(route('nilai.index', $this->mataKuliah))
            ->post(route('nilai.simpan', $this->mataKuliah), $this->payload($nilai));
    }

    /**
     * @param  array<int, float|int|string>  $nilai
     * @return array<string, mixed>
     */
    private function payload(array $nilai): array
    {
        $perKomponen = [];
        foreach ($this->mataKuliah->komponens as $i => $komponen) {
            $perKomponen[$komponen->id] = $nilai[$i];
        }

        return [
            'mata_kuliah_id' => $this->mataKuliah->id,
            'kelas_id' => $this->kelas->id,
            'nilai' => [
                $this->mahasiswa->id => $perKomponen,
            ],
        ];
    }
}
