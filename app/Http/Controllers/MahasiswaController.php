<?php

namespace App\Http\Controllers;

use App\Http\Requests\MahasiswaRequest;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    /**
     * Daftar mahasiswa milik dosen dengan filter dan pencarian.
     */
    public function index(Request $request): View
    {
        $mataKuliahs = auth()->user()->mataKuliahs()
            ->with(['kelas' => fn ($q) => $q->orderBy('nama_kelas')])
            ->orderBy('semester')
            ->orderBy('nama')
            ->get();

        $kelasIds = $mataKuliahs->flatMap->kelas->pluck('id');

        $filterMk = (string) $request->query('mata_kuliah', 'all');
        $filterKelas = (string) $request->query('kelas', 'all');
        $keyword = trim((string) $request->query('q', ''));

        $query = Mahasiswa::with('kelas.mataKuliah')
            ->whereIn('kelas_id', $kelasIds);

        if ($filterKelas !== '' && $filterKelas !== 'all') {
            $query->where('kelas_id', (int) $filterKelas);
        } elseif ($filterMk !== '' && $filterMk !== 'all') {
            $query->whereIn('kelas_id', $mataKuliahs->where('id', (int) $filterMk)->flatMap->kelas->pluck('id'));
        }

        if ($keyword !== '') {
            $query->where(function ($w) use ($keyword) {
                $w->where('nim', 'like', "%{$keyword}%")
                    ->orWhere('nama', 'like', "%{$keyword}%");
            });
        }

        $mahasiswas = $query->orderBy('nim')->get();

        $kelasFilter = $mataKuliahs->flatMap->kelas;
        if ($filterMk !== '' && $filterMk !== 'all') {
            $kelasFilter = $mataKuliahs->where('id', (int) $filterMk)->flatMap->kelas;
        }

        return view('mahasiswa.index', compact('mataKuliahs', 'mahasiswas', 'kelasFilter', 'filterMk', 'filterKelas', 'keyword'));
    }

    public function store(MahasiswaRequest $request): RedirectResponse
    {
        $kelas = $this->kelasMilikDosen($request->integer('kelas_id'));

        $kelas->mahasiswas()->create($request->validated());

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function update(MahasiswaRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $this->authorizeMahasiswa($mahasiswa);
        $this->kelasMilikDosen($request->integer('kelas_id'));

        $mahasiswa->update($request->validated());

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        $this->authorizeMahasiswa($mahasiswa);

        $mahasiswa->delete();

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil dihapus.');
    }

    /**
     * Pastikan kelas tujuan milik dosen yang sedang login.
     */
    protected function kelasMilikDosen(int $kelasId): Kelas
    {
        $kelas = Kelas::with('mataKuliah')->findOrFail($kelasId);

        abort_unless($kelas->mataKuliah->user_id === auth()->id(), 403, 'Anda tidak berhak menambah mahasiswa pada kelas ini.');

        return $kelas;
    }
}
