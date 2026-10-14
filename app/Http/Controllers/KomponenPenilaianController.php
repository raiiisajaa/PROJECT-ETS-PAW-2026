<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanKomponenRequest;
use App\Models\KomponenPenilaian;
use App\Models\MataKuliah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KomponenPenilaianController extends Controller
{
    /**
     * Halaman pemilih mata kuliah sebelum mengatur komponen.
     */
    public function pilih(): View|RedirectResponse
    {
        $mataKuliahs = auth()->user()->mataKuliahs()->orderBy('semester')->orderBy('nama')->get();

        if ($mataKuliahs->count() === 1) {
            return redirect()->route('komponen.index', $mataKuliahs->first());
        }

        return view('komponen.pilih', compact('mataKuliahs'));
    }

    /**
     * Tampilkan komponen penilaian & bobot satu mata kuliah (FR-10, FR-12).
     */
    public function index(MataKuliah $mataKuliah): View
    {
        $this->authorizeMataKuliah($mataKuliah);

        $mataKuliah->load(['komponens' => fn ($q) => $q->orderBy('id')]);

        $mataKuliahs = auth()->user()->mataKuliahs()->orderBy('semester')->orderBy('nama')->get();

        return view('komponen.index', compact('mataKuliah', 'mataKuliahs'));
    }

    /**
     * Simpan seluruh komponen sekaligus (FR-11, FR-13, FR-14, FR-15).
     * Validasi total bobot 100% dilakukan di SimpanKomponenRequest (server).
     */
    public function simpan(SimpanKomponenRequest $request, MataKuliah $mataKuliah): RedirectResponse
    {
        $this->authorizeMataKuliah($mataKuliah);

        $rows = $request->validated('komponens');

        DB::transaction(function () use ($mataKuliah, $rows) {
            $idLama = $mataKuliah->komponens()->pluck('id')->all();
            $idDipakai = [];

            foreach ($rows as $row) {
                $bobotDesimal = round(((float) $row['bobot']) / 100, 2);

                if (! empty($row['id']) && in_array((int) $row['id'], $idLama, true)) {
                    $komponen = KomponenPenilaian::find((int) $row['id']);
                    $komponen->update([
                        'nama' => $row['nama'],
                        'bobot' => $bobotDesimal,
                    ]);
                } else {
                    $komponen = $mataKuliah->komponens()->create([
                        'nama' => $row['nama'],
                        'bobot' => $bobotDesimal,
                    ]);
                }

                $idDipakai[] = $komponen->id;
            }

            // Komponen yang dihapus dari form ikut dihapus dari database
            // (nilai terkait ikut terhapus karena cascade).
            $mataKuliah->komponens()->whereNotIn('id', $idDipakai)->delete();
        });

        return redirect()
            ->route('komponen.index', $mataKuliah)
            ->with('success', 'Komponen penilaian berhasil disimpan. Nilai akhir seluruh mahasiswa otomatis mengikuti bobot terbaru.');
    }
}
