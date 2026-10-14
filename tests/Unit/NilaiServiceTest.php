<?php

namespace Tests\Unit;

use App\Models\KomponenPenilaian;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Services\NilaiService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NilaiServiceTest extends TestCase
{
    private NilaiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NilaiService();
    }

    /**
     * Skenario 8 & 9 (dan validasi batas rentang): pemetaan grade.
     */
    #[DataProvider('gradeProvider')]
    public function test_pemetaan_grade_dan_status(float $nilaiAkhir, string $grade, string $status): void
    {
        $this->assertSame($grade, $this->service->tentukanGrade($nilaiAkhir));
        $this->assertSame($status, $this->service->tentukanStatus($nilaiAkhir));
    }

    public static function gradeProvider(): array
    {
        return [
            '100 -> A' => [100.0, 'A', 'Lulus'],
            '85 -> A' => [85.0, 'A', 'Lulus'],
            '84.99 -> AB' => [84.99, 'AB', 'Lulus'],
            '84.50 -> AB (batas desimal)' => [84.50, 'AB', 'Lulus'],
            '80 -> AB' => [80.0, 'AB', 'Lulus'],
            '79.99 -> B' => [79.99, 'B', 'Lulus'],
            '70 -> B' => [70.0, 'B', 'Lulus'],
            '69.99 -> BC' => [69.99, 'BC', 'Lulus'],
            '65 -> BC' => [65.0, 'BC', 'Lulus'],
            '64.99 -> C' => [64.99, 'C', 'Lulus'],
            '55 -> C' => [55.0, 'C', 'Lulus'],
            '54.99 -> D' => [54.99, 'D', 'Tidak Lulus'],
            '54.75 -> D (batas desimal)' => [54.75, 'D', 'Tidak Lulus'],
            '49 -> D' => [49.0, 'D', 'Tidak Lulus'],
            '40 -> D' => [40.0, 'D', 'Tidak Lulus'],
            '39.99 -> E' => [39.99, 'E', 'Tidak Lulus'],
            '0 -> E' => [0.0, 'E', 'Tidak Lulus'],
        ];
    }

    /**
     * Contoh perhitungan PRD: 80(20%) + 90(20%) + 75(25%) + 85(35%) = 82,50 -> AB.
     */
    public function test_nilai_akhir_dihitung_sesuai_rumus_contoh_prd(): void
    {
        [$mahasiswa, $komponens] = $this->skenario([80, 90, 75, 85]);

        $this->assertEqualsWithDelta(82.50, $this->service->hitungNilaiAkhir($mahasiswa, $komponens), 0.0001);

        $ringkasan = $this->service->ringkasan($mahasiswa, $komponens);
        $this->assertSame(82.50, $ringkasan['nilai_akhir']);
        $this->assertSame('AB', $ringkasan['grade']);
        $this->assertSame('Lulus', $ringkasan['status']);
        $this->assertTrue($ringkasan['lengkap']);
    }

    public function test_kontribusi_tiap_komponen_sesuai_nilai_kali_bobot(): void
    {
        [$mahasiswa, $komponens] = $this->skenario([80, 90, 75, 85]);

        $detail = $this->service->ringkasan($mahasiswa, $komponens)['detail'];

        $this->assertSame(16.00, $detail[0]['kontribusi']);
        $this->assertSame(18.00, $detail[1]['kontribusi']);
        $this->assertSame(18.75, $detail[2]['kontribusi']);
        $this->assertSame(29.75, $detail[3]['kontribusi']);
    }

    public function test_nilai_kosong_dianggap_nol_dan_menandai_belum_lengkap(): void
    {
        $komponens = $this->komponens([0.20, 0.20, 0.25, 0.35]);
        $mahasiswa = new Mahasiswa(['nim' => '1', 'nama' => 'Contoh']);
        $mahasiswa->setRelation('nilais', new Collection([
            new Nilai(['komponen_id' => 1, 'nilai' => 80]),
        ]));

        $ringkasan = $this->service->ringkasan($mahasiswa, $komponens);

        $this->assertFalse($ringkasan['lengkap']);
        $this->assertEqualsWithDelta(16.00, $ringkasan['nilai_akhir'], 0.0001);
    }

    /**
     * Skenario 5, 6, 7: validasi total bobot.
     */
    public function test_total_bobot_valid_hanya_ketika_tepat_satu(): void
    {
        $this->assertTrue($this->service->totalBobotValid($this->komponens([0.20, 0.20, 0.25, 0.35]))); // 100%
        $this->assertFalse($this->service->totalBobotValid($this->komponens([0.20, 0.20, 0.25, 0.25]))); // 90%
        $this->assertFalse($this->service->totalBobotValid($this->komponens([0.30, 0.30, 0.25, 0.25]))); // 110%
    }

    /**
     * @param  array<int, float|int>  $nilai
     * @return array{0: Mahasiswa, 1: Collection<int, KomponenPenilaian>}
     */
    private function skenario(array $nilai): array
    {
        $komponens = $this->komponens([0.20, 0.20, 0.25, 0.35]);

        $mahasiswa = new Mahasiswa(['nim' => '1', 'nama' => 'Contoh']);

        $nilais = new Collection();
        foreach ($nilai as $i => $n) {
            $nilais->push(new Nilai(['komponen_id' => $i + 1, 'nilai' => $n]));
        }
        $mahasiswa->setRelation('nilais', $nilais);

        return [$mahasiswa, $komponens];
    }

    /**
     * @param  array<int, float>  $bobot
     * @return Collection<int, KomponenPenilaian>
     */
    private function komponens(array $bobot): Collection
    {
        $nama = ['Tugas 1', 'Tugas 2', 'UTS', 'UAS'];

        $komponens = new Collection();
        foreach ($bobot as $i => $b) {
            $komponen = new KomponenPenilaian(['nama' => $nama[$i], 'bobot' => $b]);
            $komponen->id = $i + 1;
            $komponens->push($komponen);
        }

        return $komponens;
    }
}
