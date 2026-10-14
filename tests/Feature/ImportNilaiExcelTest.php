<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Support\BuildsPenilaianData;
use Tests\TestCase;

class ImportNilaiExcelTest extends TestCase
{
    use BuildsPenilaianData;
    use RefreshDatabase;

    public function test_impor_nilai_valid_menyimpan_data(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen);
        $kelas = $this->kelas($mataKuliah);
        $mahasiswa = $this->mahasiswa($kelas, ['nim' => '2210000001']);

        $berkas = $this->berkasExcel(
            ['nim', 'nama', 'Tugas 1', 'Tugas 2', 'UTS', 'UAS'],
            [['2210000001', 'Nama Contoh', 80, 90, 75, 85]]
        );

        $this->actingAs($dosen)
            ->post(route('nilai.import', $mataKuliah), ['kelas_id' => $kelas->id, 'berkas' => $berkas])
            ->assertRedirect(route('nilai.index', ['mataKuliah' => $mataKuliah->id, 'kelas' => $kelas->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('nilai', 4);
        $this->assertDatabaseHas('nilai', [
            'mahasiswa_id' => $mahasiswa->id,
            'komponen_id' => $mataKuliah->komponens[0]->id,
            'nilai' => 80,
        ]);
    }

    public function test_baris_bermasalah_ditandai_tanpa_membatalkan_baris_lain(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen);
        $kelas = $this->kelas($mataKuliah);
        $valid = $this->mahasiswa($kelas, ['nim' => '2210000002']);
        $this->mahasiswa($kelas, ['nim' => '2210000003']);

        $berkas = $this->berkasExcel(
            ['nim', 'nama', 'Tugas 1', 'Tugas 2', 'UTS', 'UAS'],
            [
                ['2210000002', 'Valid', 80, 80, 80, 80],
                ['2210000003', 'Tidak Valid', 150, 80, 80, 80],
                ['2299999999', 'NIM Tak Dikenal', 80, 80, 80, 80],
            ]
        );

        $this->actingAs($dosen)
            ->post(route('nilai.import', $mataKuliah), ['kelas_id' => $kelas->id, 'berkas' => $berkas])
            ->assertRedirect();

        // Baris valid tetap tersimpan (FR-22).
        $this->assertDatabaseCount('nilai', 4);
        $this->assertDatabaseHas('nilai', ['mahasiswa_id' => $valid->id, 'nilai' => 80]);

        // Dua baris bermasalah dilaporkan.
        $this->assertCount(2, session('import_gagal'));
    }

    public function test_impor_menolak_berkas_dengan_format_tidak_didukung(): void
    {
        $dosen = $this->dosen();
        $mataKuliah = $this->mataKuliah($dosen);
        $kelas = $this->kelas($mataKuliah);

        $berkas = UploadedFile::fake()->create('nilai.pdf', 100, 'application/pdf');

        $this->actingAs($dosen)
            ->post(route('nilai.import', $mataKuliah), ['kelas_id' => $kelas->id, 'berkas' => $berkas])
            ->assertSessionHasErrors('berkas');
    }

    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function berkasExcel(array $headings, array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray(array_merge([$headings], $rows), null, 'A1');

        $direktori = storage_path('app/testing');
        if (! is_dir($direktori)) {
            mkdir($direktori, 0777, true);
        }

        $path = $direktori.DIRECTORY_SEPARATOR.'impor-'.uniqid().'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'nilai-import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
