<?php

namespace Tests\Feature;

use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsPenilaianData;
use Tests\TestCase;

class DetailPenilaianTest extends TestCase
{
    use BuildsPenilaianData;
    use RefreshDatabase;

    public function test_halaman_detail_menampilkan_identitas_perhitungan_dan_grade(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen);
        $kelas = $this->kelas($mataKuliah);
        $mahasiswa = $this->mahasiswa($kelas, ['nama' => 'Budi Santoso']);

        foreach ([80, 90, 75, 85] as $i => $nilai) {
            $mahasiswa->nilais()->create(['komponen_id' => $mataKuliah->komponens[$i]->id, 'nilai' => $nilai]);
        }

        $this->actingAs($dosen)
            ->get(route('nilai.detail', $mahasiswa))
            ->assertOk()
            ->assertSee($mahasiswa->nim)
            ->assertSee('Budi Santoso')
            ->assertSee('82,50')
            ->assertSee('AB');
    }

    public function test_feedback_dosen_dapat_disimpan(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen);
        $kelas = $this->kelas($mataKuliah);
        $mahasiswa = $this->mahasiswa($kelas);

        $this->actingAs($dosen)
            ->post(route('nilai.feedback', $mahasiswa), ['isi' => 'Tingkatkan latihan pada bagian UAS.'])
            ->assertRedirect();

        $this->assertDatabaseHas('feedback', [
            'mahasiswa_id' => $mahasiswa->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'isi' => 'Tingkatkan latihan pada bagian UAS.',
        ]);
    }

    public function test_feedback_duplikat_diperbarui_bukan_ditambah(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen);
        $kelas = $this->kelas($mataKuliah);
        $mahasiswa = $this->mahasiswa($kelas);

        Feedback::create([
            'mahasiswa_id' => $mahasiswa->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'isi' => 'Feedback lama',
        ]);

        $this->actingAs($dosen)->post(route('nilai.feedback', $mahasiswa), ['isi' => 'Feedback baru']);

        $this->assertDatabaseCount('feedback', 1);
        $this->assertDatabaseHas('feedback', ['isi' => 'Feedback baru']);
    }

    public function test_dosen_tidak_dapat_melihat_detail_mahasiswa_dosen_lain(): void
    {
        $dosen = $this->dosen();
        $dosenLain = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosenLain);
        $kelas = $this->kelas($mataKuliah);
        $mahasiswa = $this->mahasiswa($kelas);

        $this->actingAs($dosen)
            ->get(route('nilai.detail', $mahasiswa))
            ->assertForbidden();
    }
}
