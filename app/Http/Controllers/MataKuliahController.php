<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MataKuliahController extends Controller
{
    /**
     * Daftar mata kuliah yang diampu dosen (FR-08).
     */
    public function index(): View
    {
        $mataKuliahs = auth()->user()->mataKuliahs()
            ->withCount('kelas', 'komponens')
            ->withCount(['mahasiswas'])
            ->orderBy('semester')
            ->orderBy('nama')
            ->get();

        return view('mata-kuliah.index', compact('mataKuliahs'));
    }
}
