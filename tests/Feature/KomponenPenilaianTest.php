<?php

namespace Tests\Feature;

use App\Services\NilaiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsPenilaianData;
use Tests\TestCase;

class KomponenPenilaianTest extends TestCase
{
    use BuildsPenilaianData;
    use RefreshDatabase;

    /**
     * Skenario 5: total bobot 100% diterima.
     */
    public function test_total_bobot_100_persen_diterima(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen, []);

        $response = $this->actingAs($dosen)->post(
            route('komponen.simpan', $mataKuliah),
            $this->payload([20, 20, 25, 35])
        );

        $response->assertRedirect(route('komponen.index', $mataKuliah));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseCount('komponen_penilaian', 4);
        $this->assertDatabaseHas('komponen_penilaian', [
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'Tugas 1',
            'bobot' => 0.20,
        ]);
        $this->assertDatabaseHas('komponen_penilaian', [
            'mata_kuliah_id' => $mataKuliah->id,
            'nama' => 'UAS',
            'bobot' => 0.35,
        ]);
    }

    /**
     * Skenario 6: total bobot 90% ditolak.
     */
    public function test_total_bobot_90_persen_ditolak(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen, []);

        $this->actingAs($dosen)
            ->from(route('komponen.index', $mataKuliah))
            ->post(route('komponen.simpan', $mataKuliah), $this->payload([20, 20, 25, 25]))
            ->assertRedirect(route('komponen.index', $mataKuliah))
            ->assertSessionHasErrors('komponens');

        $this->assertDatabaseCount('komponen_penilaian', 0);
    }

    /**
     * Skenario 7: total bobot 110% ditolak.
     */
    public function test_total_bobot_110_persen_ditolak(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen, []);

        $this->actingAs($dosen)
            ->from(route('komponen.index', $mataKuliah))
            ->post(route('komponen.simpan', $mataKuliah), $this->payload([30, 30, 25, 25]))
            ->assertSessionHasErrors('komponens');

        $this->assertDatabaseCount('komponen_penilaian', 0);
    }

    /**
     * Skenario 12 (bagian komponen): server tetap menolak walau validasi browser dilewati.
     */
    public function test_server_tetap_menolak_total_bobot_bukan_100(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen, []);

        $this->actingAs($dosen)
            ->post(route('komponen.simpan', $mataKuliah), $this->payload([50, 30]))
            ->assertSessionHasErrors('komponens');

        $this->assertDatabaseCount('komponen_penilaian', 0);
    }

    /**
     * Skenario 10 / FR-15: setelah bobot berubah, nilai akhir ikut berubah.
     */
    public function test_perubahan_bobot_mengubah_nilai_akhir_mahasiswa(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen);
        $kelas = $this->kelas($mataKuliah);
        $mahasiswa = $this->mahasiswa($kelas);

        // Beri nilai 100 pada semua komponen => nilai akhir 100 (grade A).
        foreach ($mataKuliah->komponens as $komponen) {
            $mahasiswa->nilais()->create(['komponen_id' => $komponen->id, 'nilai' => 100]);
        }

        $service = app(NilaiService::class);
        $sebelum = $service->ringkasan($mahasiswa->fresh('nilais'), $mataKuliah->komponens)['nilai_akhir'];

        // Ubah bobot (total tetap 100%) lalu set ulang nilai.
        $this->actingAs($dosen)->post(route('komponen.simpan', $mataKuliah), [
            'komponens' => [
                ['id' => $mataKuliah->komponens[0]->id, 'nama' => 'Tugas 1', 'bobot' => 40],
                ['id' => $mataKuliah->komponens[1]->id, 'nama' => 'Tugas 2', 'bobot' => 10],
                ['id' => $mataKuliah->komponens[2]->id, 'nama' => 'UTS', 'bobot' => 20],
                ['id' => $mataKuliah->komponens[3]->id, 'nama' => 'UAS', 'bobot' => 30],
            ],
        ])->assertSessionHasNoErrors();

        // Set nilai baru: Tugas 1 = 100, sisanya 0 => hanya menyumbang 40%.
        $mataKuliah->refresh()->load('komponens');
        $mahasiswa->nilais()->delete();
        $mahasiswa->nilais()->create(['komponen_id' => $mataKuliah->komponens[0]->id, 'nilai' => 100]);
        $mahasiswa->nilais()->create(['komponen_id' => $mataKuliah->komponens[1]->id, 'nilai' => 0]);
        $mahasiswa->nilais()->create(['komponen_id' => $mataKuliah->komponens[2]->id, 'nilai' => 0]);
        $mahasiswa->nilais()->create(['komponen_id' => $mataKuliah->komponens[3]->id, 'nilai' => 0]);

        $sesudah = $service->ringkasan($mahasiswa->fresh('nilais'), $mataKuliah->komponens)['nilai_akhir'];

        $this->assertSame(100.0, $sebelum);
        $this->assertSame(40.0, $sesudah);
    }

    public function test_komponen_milik_dosen_lain_tidak_dapat_diubah(): void
    {
        $dosen = $this->dosen();
        $dosenLain = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosenLain, []);

        $this->actingAs($dosen)
            ->post(route('komponen.simpan', $mataKuliah), $this->payload([20, 20, 25, 35]))
            ->assertForbidden();
    }

    /**
     * @param  array<int, float|int>  $bobotPersen
     * @return array{komponens: array<int, array{id: null, nama: string, bobot: float|int}>}
     */
    private function payload(array $bobotPersen): array
    {
        $nama = ['Tugas 1', 'Tugas 2', 'UTS', 'UAS'];

        $rows = [];
        foreach ($bobotPersen as $i => $bobot) {
            $rows[] = ['id' => null, 'nama' => $nama[$i], 'bobot' => $bobot];
        }

        return ['komponens' => $rows];
    }
}
