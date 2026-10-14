<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Pastikan mata kuliah milik dosen yang sedang login (kebutuhan Keamanan PRD).
     */
    protected function authorizeMataKuliah(MataKuliah $mataKuliah): void
    {
        abort_unless($mataKuliah->user_id === auth()->id(), 403, 'Anda tidak berhak mengakses mata kuliah ini.');
    }

    /**
     * Pastikan mahasiswa berada pada mata kuliah milik dosen yang sedang login.
     */
    protected function authorizeMahasiswa(Mahasiswa $mahasiswa): void
    {
        $mahasiswa->loadMissing('kelas.mataKuliah');

        abort_unless($mahasiswa->kelas->mataKuliah->user_id === auth()->id(), 403, 'Anda tidak berhak mengakses data mahasiswa ini.');
    }
}
