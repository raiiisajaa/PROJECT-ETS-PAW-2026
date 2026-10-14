<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KomponenPenilaianController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Sistem Penilaian Tugas Mahasiswa
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

// Autentikasi (FR-01 .. FR-04)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Seluruh halaman berikut hanya dapat diakses setelah login (FR-03)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mata-kuliah', [MataKuliahController::class, 'index'])->name('mata-kuliah.index');

    // Pengaturan komponen penilaian (FR-10 .. FR-15)
    Route::get('/komponen', [KomponenPenilaianController::class, 'pilih'])->name('komponen.pilih');
    Route::get('/mata-kuliah/{mataKuliah}/komponen', [KomponenPenilaianController::class, 'index'])->name('komponen.index');
    Route::post('/mata-kuliah/{mataKuliah}/komponen', [KomponenPenilaianController::class, 'simpan'])->name('komponen.simpan');

    // Input nilai (FR-16 .. FR-22)
    Route::get('/penilaian', [NilaiController::class, 'pilih'])->name('nilai.pilih');
    Route::get('/mata-kuliah/{mataKuliah}/nilai', [NilaiController::class, 'index'])->name('nilai.index');
    Route::post('/mata-kuliah/{mataKuliah}/nilai', [NilaiController::class, 'simpan'])->name('nilai.simpan');
    Route::post('/mata-kuliah/{mataKuliah}/nilai/import', [NilaiController::class, 'import'])->name('nilai.import');
    Route::get('/mata-kuliah/{mataKuliah}/nilai/template', [NilaiController::class, 'template'])->name('nilai.template');

    // Detail penilaian & feedback (FR-23 .. FR-27)
    Route::get('/mahasiswa/{mahasiswa}/penilaian', [NilaiController::class, 'detail'])->name('nilai.detail');
    Route::post('/mahasiswa/{mahasiswa}/penilaian', [NilaiController::class, 'simpanFeedback'])->name('nilai.feedback');

    // Data mahasiswa
    Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::post('/mahasiswa', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
    Route::put('/mahasiswa/{mahasiswa}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');
    Route::delete('/mahasiswa/{mahasiswa}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');

    // Rekapitulasi (FR-28 .. FR-32)
    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/export', [RekapController::class, 'export'])->name('rekap.export');

    // Profil
    Route::get('/profil', [ProfileController::class, 'show'])->name('profil');
});
