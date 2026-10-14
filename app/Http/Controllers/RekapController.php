<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Services\NilaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RekapController extends Controller
{
    public function __construct(protected NilaiService $nilaiService)
    {
    }

    /**
     * Rekapitulasi nilai dengan filter mata kuliah, kelas, dan pencarian
     * (FR-28, FR-29, FR-30, FR-32).
     */
    public function index(Request $request): View
    {
        [$mataKuliahs, $rows, $ringkasan] = $this->kumpulkan($request);

        $filters = [
            'mata_kuliah' => $request->query('mata_kuliah', 'all'),
            'kelas' => $request->query('kelas', 'all'),
            'q' => $request->query('q', ''),
        ];

        $kelasFilter = $this->kelasUntukFilter($filters['mata_kuliah']);

        return view('rekap.index', compact('mataKuliahs', 'rows', 'ringkasan', 'filters', 'kelasFilter'));
    }

    /**
     * Ekspor rekapitulasi ke Excel sesuai filter aktif (FR-31).
     */
    public function export(Request $request): BinaryFileResponse
    {
        [, $rows] = $this->kumpulkan($request);

        $headings = ['No', 'NIM', 'Nama', 'Mata Kuliah', 'Kelas', 'Nilai Akhir', 'Grade', 'Status'];

        $data = $rows->values()->map(fn ($row, $i) => [
            $i + 1,
            $row['nim'],
            $row['nama'],
            $row['mata_kuliah'],
            $row['kelas'],
            number_format($row['nilai_akhir'], 2, ',', '.'),
            $row['grade'],
            $row['status'],
        ]);

        $namaBerkas = 'rekap-nilai-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(new \App\Exports\RekapExport($headings, $data), $namaBerkas);
    }

    /**
     * Bangun dataset rekap berdasarkan filter.
     *
     * @return array{0: Collection<int, MataKuliah>, 1: Collection<int, array<string, mixed>>, 2: array<string, mixed>}
     */
    protected function kumpulkan(Request $request): array
    {
        $mataKuliahs = auth()->user()->mataKuliahs()
            ->with('kelas')
            ->orderBy('semester')
            ->orderBy('nama')
            ->get();

        $filterMk = (string) $request->query('mata_kuliah', 'all');
        $filterKelas = (string) $request->query('kelas', 'all');
        $keyword = trim((string) $request->query('q', ''));

        $daftarMk = ($filterMk !== '' && $filterMk !== 'all')
            ? $mataKuliahs->where('id', (int) $filterMk)
            : $mataKuliahs;

        $rows = collect();

        foreach ($daftarMk as $mataKuliah) {
            $mataKuliah->load(['komponens' => fn ($q) => $q->orderBy('id')]);

            $daftarKelas = ($filterKelas !== '' && $filterKelas !== 'all')
                ? $mataKuliah->kelas->where('id', (int) $filterKelas)
                : $mataKuliah->kelas;

            foreach ($daftarKelas as $kelas) {
                $query = $kelas->mahasiswas()->with('nilais');

                if ($keyword !== '') {
                    $query->where(function ($w) use ($keyword) {
                        $w->where('nim', 'like', "%{$keyword}%")
                            ->orWhere('nama', 'like', "%{$keyword}%");
                    });
                }

                foreach ($query->orderBy('nim')->get() as $mahasiswa) {
                    $ringkasan = $this->nilaiService->ringkasan($mahasiswa, $mataKuliah->komponens);

                    $rows->push([
                        'nim' => $mahasiswa->nim,
                        'nama' => $mahasiswa->nama,
                        'kelas' => $kelas->nama_kelas,
                        'mata_kuliah' => $mataKuliah->nama,
                        'nilai_akhir' => $ringkasan['nilai_akhir'],
                        'grade' => $ringkasan['grade'],
                        'status' => $ringkasan['status'],
                    ]);
                }
            }
        }

        $ringkasan = [
            'jumlah' => $rows->count(),
            'rata_rata' => $rows->count() ? round($rows->avg('nilai_akhir'), 2) : 0.0,
            'lulus' => $rows->where('status', 'Lulus')->count(),
            'tidak_lulus' => $rows->where('status', 'Tidak Lulus')->count(),
        ];

        return [$mataKuliahs, $rows, $ringkasan];
    }

    /**
     * Daftar kelas untuk dropdown filter (mengikuti mata kuliah terpilih).
     *
     * @return Collection<int, Kelas>
     */
    protected function kelasUntukFilter(string $filterMk): Collection
    {
        $query = Kelas::query()->whereIn(
            'mata_kuliah_id',
            auth()->user()->mataKuliahs()->pluck('id')
        );

        if ($filterMk !== '' && $filterMk !== 'all') {
            $query->where('mata_kuliah_id', (int) $filterMk);
        }

        return $query->with('mataKuliah')->orderBy('mata_kuliah_id')->orderBy('nama_kelas')->get();
    }
}
