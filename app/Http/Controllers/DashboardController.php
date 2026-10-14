<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\KomponenPenilaian;
use App\Models\Mahasiswa;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard dosen (FR-06, FR-07, FR-08).
     */
    public function index(): View
    {
        $user = auth()->user();

        $mataKuliahs = $user->mataKuliahs()
            ->withCount('kelas')
            ->orderBy('semester')
            ->orderBy('nama')
            ->get();

        $jumlahMataKuliah = $mataKuliahs->count();

        $kelasIds = Kelas::whereIn('mata_kuliah_id', $mataKuliahs->pluck('id'))->pluck('id');

        $jumlahMahasiswa = Mahasiswa::whereIn('kelas_id', $kelasIds)->count();
        $jumlahKelas = $kelasIds->count();
        $jumlahKomponen = KomponenPenilaian::whereIn('mata_kuliah_id', $mataKuliahs->pluck('id'))->count();

        return view('dashboard', compact(
            'user',
            'mataKuliahs',
            'jumlahMataKuliah',
            'jumlahMahasiswa',
            'jumlahKelas',
            'jumlahKomponen'
        ));
    }
}
