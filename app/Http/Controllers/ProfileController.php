<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Halaman profil dosen (FR-04 menu profil).
     */
    public function show(): View
    {
        $user = auth()->user();

        $jumlahMataKuliah = $user->mataKuliahs()->count();

        return view('profil', compact('user', 'jumlahMataKuliah'));
    }
}
