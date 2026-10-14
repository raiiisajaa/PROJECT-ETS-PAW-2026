# Hasil Pengujian

Pengujian dijalankan dengan perintah:

```bash
php artisan test
```

Hasil akhir: **53 test lulus, 172 assertions** (SQLite in-memory, isolasi penuh
dari database MySQL development).

```
Tests:    53 passed (172 assertions)
```

---

## A. Peta 12 Skenario PRD → Test

| # | Skenario (PRD) | Test yang membuktikan | Kelas | Hasil |
| --- | --- | --- | --- | --- |
| 1 | Penilaian valid **80** diterima | `test_nilai_80_diterima` | `InputNilaiTest` | ✅ PASS |
| 2 | Penilaian valid **100** diterima | `test_nilai_100_diterima` | `InputNilaiTest` | ✅ PASS |
| 3 | Nilai **101** ditolak (>100) | `test_nilai_101_ditolak` | `InputNilaiTest` | ✅ PASS |
| 4 | Nilai **−1** ditolak (<0) | `test_nilai_minus_satu_ditolak` | `InputNilaiTest` | ✅ PASS |
| 5 | Total bobot **100%** diterima | `test_total_bobot_100_persen_diterima` | `KomponenPenilaianTest` | ✅ PASS |
| 6 | Total bobot **90%** ditolak | `test_total_bobot_90_persen_ditolak` | `KomponenPenilaianTest` | ✅ PASS |
| 7 | Total bobot **110%** ditolak | `test_total_bobot_110_persen_ditolak` | `KomponenPenilaianTest` | ✅ PASS |
| 8 | Nilai akhir **85 → grade A** | `test_nilai_akhir_85_menghasilkan_grade_a` + `test_pemetaan_grade_dan_status` (85→A) | `InputNilaiTest` & `NilaiServiceTest` | ✅ PASS |
| 9 | Nilai akhir **49 → grade D** | `test_nilai_akhir_49_menghasilkan_grade_d` + `test_pemetaan_grade_dan_status` (49→D) | `InputNilaiTest` & `NilaiServiceTest` | ✅ PASS |
| 10 | **Perubahan bobot** mengubah nilai akhir | `test_perubahan_bobot_mengubah_nilai_akhir_mahasiswa` (20,0 → 40,0) | `KomponenPenilaianTest` | ✅ PASS |
| 11 | Nilai **bukan angka** ditolak | `test_nilai_bukan_angka_ditolak` | `InputNilaiTest` | ✅ PASS |
| 12 | Validasi **server** tetap bekerja walau validasi **browser** dilewati | `test_laravel_tetap_memvalidasi_walau_validasi_browser_dilewati` (nilai 150) + `test_server_tetap_menolak_total_bobot_bukan_100` (bobot 80%) | `InputNilaiTest` & `KomponenPenilaianTest` | ✅ PASS |

> Catatan skenario 12: test mengirim permintaan HTTP langsung ke server
> (tanpa JavaScript), membuktikan validasi berlapis Form Request tetap
> menolak data di luar rentang secara mandiri.

---

## B. Cakupan Fungsional Tambahan (FR)

| Test | Kelas | Hasil |
| --- | --- | --- |
| Rumus perhitungan PRD: 80/90/75/85 → **82,50 → AB → Lulus** | `NilaiServiceTest` | ✅ PASS |
| Kontribusi tiap komponen = Nilai × Bobot (16,00 / 18,00 / 18,75 / 29,75) | `NilaiServiceTest` | ✅ PASS |
| Pembulatan & batas grade (`84,99→AB`, `84,50→AB`, `54,75→D`, `39,99→E`) | `NilaiServiceTest` | ✅ PASS |
| Validasi total bobot 90%/110% ditolak di lapisan service | `NilaiServiceTest` | ✅ PASS |
| Nilai kosong → dianggap 0 & ditandai belum lengkap | `NilaiServiceTest` | ✅ PASS |
| Login berhasil & gagal, logout, akses halaman tanpa login | `AuthenticationTest` | ✅ PASS |
| Semua route utama terdaftar (`/dashboard`, komponen, nilai, rekap, …) | `RoutingTest` | ✅ PASS |
| Pembatasan akses: komponen/nilai/detail milik dosen lain → **403** | `KomponenPenilaianTest`, `DetailPenilaianTest` | ✅ PASS |
| Halaman rekap menampilkan 82,50 / AB / Lulus | `RekapNilaiTest` | ✅ PASS |
| Pencarian rekap berdasar NIM/nama | `RekapNilaiTest` | ✅ PASS |
| Export rekap Excel mengembalikan berkas (`assertDownload`) | `RekapNilaiTest` | ✅ PASS |
| Detail mahasiswa: identitas, perhitungan, grade | `DetailPenilaianTest` | ✅ PASS |
| Feedback disimpan & duplikat diperbarui (bukan ditambah) | `DetailPenilaianTest` | ✅ PASS |
| Import Excel valid tersimpan (4 nilai) | `ImportNilaiExcelTest` | ✅ PASS |
| Import: baris bermasalah ditandai, baris lain tetap tersimpan | `ImportNilaiExcelTest` | ✅ PASS |
| Import menolak format tak didukung (PDF) | `ImportNilaiExcelTest` | ✅ PASS |

---

## C. Ringkasan Hasil Berkala

```
small descriptor        53 passed, 172 assertions
```

Perbaruan nilai saat penulisan dokumen:

| Tahap | Status |
| --- | --- |
| Rangkaian test awal | ❌ beberapa gagal (urutan komponen tak deterministik, baris Excel berupa Collection, root redirect) — **sudah diperbaiki** |
| Rangkaian setelah perbaikan | ✅ **53 passed (172 assertions)** |

Perbaikan yang dilakukan selama pengujian:

1. **Urutan komponen deterministik** — relasi `komponens()` diberi `orderBy('id')`
   (+ `sortBy('id')` di `NilaiService`) sehingga skenario 10 dan perhitungan
   konsisten di semua halaman/database.
2. **Baris hasil import** — Laravel Excel menyerahkan tiap baris sebagai
   `Collection`; `NilaiImport` kini menormalkan ke array sebelum diverifikasi.
3. **Baris import bermasalah** — seluruh baris dilewati bila salah satu sel
   tidak valid (bukan hanya sel tersebut), sesuai semantik "baris ditandai".
4. **Redirect `/`** untuk tamu diarahkan langsung ke `/login`.

---

## D. Uji Manual (Opsional, Web Nyata di `php artisan serve`)

1. Login `raiiisaja@gmail.com` / `password`.
2. Dashboard → pilih mata kuliah → **Komponen & Bobot**: lihat total 100%;
   coba ubah menjadi total 90% → aksi blokir (JS) lalu tolak (server).
3. **Input Nilai**: ketik 80 → tersimpan; ketik 101 → ditolak; ketik `abc` → ditolak.
4. Import: unduh **Template Excel**, isi, upload kembali → data tersimpan.
5. Detail mahasiswa → feedback → tersimpan.
6. Rekap → filter → **Export Excel** → berkas `.xlsx` terunduh.