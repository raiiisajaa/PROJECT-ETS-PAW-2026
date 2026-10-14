<?php

namespace App\Http\Controllers;

use App\Exports\TemplateNilaiExport;
use App\Http\Requests\ImportNilaiRequest;
use App\Http\Requests\SimpanFeedbackRequest;
use App\Http\Requests\SimpanNilaiRequest;
use App\Imports\NilaiImport;
use App\Models\Feedback;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Services\NilaiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NilaiController extends Controller
{
    public function __construct(protected NilaiService $nilaiService)
    {
    }

    /**
     * Halaman pemilih mata kuliah untuk input nilai.
     */
    public function pilih(): View|RedirectResponse
    {
        $mataKuliahs = auth()->user()->mataKuliahs()->orderBy('semester')->orderBy('nama')->get();

        if ($mataKuliahs->count() === 1) {
            return redirect()->route('nilai.index', $mataKuliahs->first());
        }

        return view('nilai.pilih', compact('mataKuliahs'));
    }

    /**
     * Tampilkan tabel input nilai per kelas (FR-16, FR-17, FR-18).
     */
    public function index(Request $request, MataKuliah $mataKuliah): View
    {
        $this->authorizeMataKuliah($mataKuliah);

        $mataKuliah->load([
            'komponens' => fn ($q) => $q->orderBy('id'),
            'kelas' => fn ($q) => $q->orderBy('nama_kelas'),
        ]);

        $kelasList = $mataKuliah->kelas;

        $kelas = $kelasList->firstWhere('id', (int) $request->query('kelas')) ?? $kelasList->first();

        $mahasiswas = collect();
        $ringkasan = [];

        if ($kelas) {
            $mahasiswas = $kelas->mahasiswas()->with('nilais')->orderBy('nim')->get();

            foreach ($mahasiswas as $mahasiswa) {
                $ringkasan[$mahasiswa->id] = $this->nilaiService->ringkasan($mahasiswa, $mataKuliah->komponens);
            }
        }

        $importGagal = session('import_gagal', []);

        return view('nilai.index', compact(
            'mataKuliah',
            'kelasList',
            'kelas',
            'mahasiswas',
            'ringkasan',
            'importGagal'
        ));
    }

    /**
     * Simpan seluruh nilai sekaligus (FR-19, FR-21).
     */
    public function simpan(SimpanNilaiRequest $request, MataKuliah $mataKuliah): RedirectResponse
    {
        $this->authorizeMataKuliah($mataKuliah);

        $data = $request->input('nilai', []);

        DB::transaction(function () use ($data) {
            foreach ($data as $mahasiswaId => $komponens) {
                foreach ((array) $komponens as $komponenId => $value) {
                    if ($value === null || $value === '') {
                        Nilai::where('mahasiswa_id', (int) $mahasiswaId)
                            ->where('komponen_id', (int) $komponenId)
                            ->delete();
                        continue;
                    }

                    Nilai::updateOrCreate(
                        [
                            'mahasiswa_id' => (int) $mahasiswaId,
                            'komponen_id' => (int) $komponenId,
                        ],
                        ['nilai' => round((float) $value, 2)]
                    );
                }
            }
        });

        return redirect()
            ->route('nilai.index', array_filter([
                'mataKuliah' => $mataKuliah->id,
                'kelas' => $request->integer('kelas_id') ?: null,
            ]))
            ->with('success', 'Nilai berhasil disimpan. Nilai akhir, grade, dan status dihitung otomatis.');
    }

    /**
     * Impor nilai dari berkas Excel (FR-20, FR-21, FR-22).
     */
    public function import(ImportNilaiRequest $request, MataKuliah $mataKuliah): RedirectResponse
    {
        $this->authorizeMataKuliah($mataKuliah);

        $kelas = $mataKuliah->kelas()->findOrFail($request->integer('kelas_id'));

        $mataKuliah->load('komponens');

        $import = new NilaiImport($mataKuliah, $kelas->id);

        Excel::import($import, $request->file('berkas'));

        $jumlahTersimpan = 0;

        if (! empty($import->berhasil)) {
            DB::transaction(function () use ($import, &$jumlahTersimpan) {
                foreach ($import->berhasil as $row) {
                    Nilai::updateOrCreate(
                        ['mahasiswa_id' => $row['mahasiswa_id'], 'komponen_id' => $row['komponen_id']],
                        ['nilai' => $row['nilai']]
                    );
                    $jumlahTersimpan++;
                }
            });
        }

        $redirect = redirect()->route('nilai.index', [
            'mataKuliah' => $mataKuliah->id,
            'kelas' => $kelas->id,
        ]);

        if ($jumlahTersimpan > 0) {
            $redirect->with('success', "Impor selesai. {$jumlahTersimpan} nilai berhasil disimpan.");
        }

        if (! empty($import->gagal)) {
            $redirect->with('error', count($import->gagal).' baris bermasalah dan tidak diimpor. Periksa daftar di bawah.');
        }

        return $redirect->with('import_gagal', $import->gagal);
    }

    /**
     * Unduh template Excel impor nilai.
     */
    public function template(MataKuliah $mataKuliah): BinaryFileResponse
    {
        $this->authorizeMataKuliah($mataKuliah);

        $mataKuliah->load('komponens');

        return Excel::download(
            new TemplateNilaiExport($mataKuliah),
            'template-nilai-'.$mataKuliah->kode.'.xlsx'
        );
    }

    /**
     * Detail penilaian & feedback satu mahasiswa (FR-23 sampai FR-27).
     */
    public function detail(Mahasiswa $mahasiswa): View
    {
        $this->authorizeMahasiswa($mahasiswa);

        $mahasiswa->load([
            'kelas.mataKuliah.komponens' => fn ($q) => $q->orderBy('id'),
            'nilais',
        ]);

        $mataKuliah = $mahasiswa->kelas->mataKuliah;

        $ringkasan = $this->nilaiService->ringkasan($mahasiswa, $mataKuliah->komponens);

        $feedback = Feedback::where('mahasiswa_id', $mahasiswa->id)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->first();

        return view('nilai.detail', compact('mahasiswa', 'mataKuliah', 'ringkasan', 'feedback'));
    }

    /**
     * Simpan feedback dosen (FR-26).
     */
    public function simpanFeedback(SimpanFeedbackRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $this->authorizeMahasiswa($mahasiswa);

        $mataKuliah = $mahasiswa->kelas->mataKuliah;

        Feedback::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id, 'mata_kuliah_id' => $mataKuliah->id],
            ['isi' => $request->validated('isi')]
        );

        return back()->with('success', 'Feedback berhasil disimpan.');
    }
}
