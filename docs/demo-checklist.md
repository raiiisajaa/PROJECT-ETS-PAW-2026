# Checklist Video Demo (≤ 10 menit)

Deliverable PRD **"video demo ≤ 10 menit"** tidak dapat direkam otomatis oleh
agen — perlu direkam **mandiri** (Loom, OBS, atau perekam layar lainnya).
Berikut skrip demo yang disarankan agar semua poin PRD terliput.

> **Sebelum merekam:** pastikan aplikasi berjalan di `http://127.0.0.1:8000`
> dan data seed sudah terpasang (`php artisan migrate:fresh --seed`).

---

## Sesi 1 — Persiapan & Login (≈ 1 menit)

- [ ] Jalankan `php artisan serve`, buka `http://127.0.0.1:8000`.
- [ ] Halaman login terbuka.
- [ ] Login sebagai dosen: `raiiisaja@gmail.com` / `password`.
- [ ] Muncul dashboard dengan ringkasan mata kuliah.

## Sesi 2 — Komponen & Bobot (≈ 1,5 menit)

- [ ] Buka salah satu mata kuliah → **Komponen & Bobot**.
- [ ] Tunjukkan bobot default: Tugas 1 (20%), Tugas 2 (20%), UTS (25%), UAS (35%),
      total **100%**.
- [ ] Ubah total menjadi 90% (mis. UAS 25%) → perlihatkan **blokir** (JS) dan
      penolakan oleh server.
- [ ] Kembalikan ke 100% dan simpan.

## Sesi 3 — Input Nilai Manual (≈ 1,5 menit)

- [ ] Buka **Input Nilai** untuk satu kelas.
- [ ] Isi nilai 80/90/75/85 pada mahasiswa pertama → simpan.
- [ ] Coba isi **101** dan **−1** → perlihatkan penolakan (JS + server).
- [ ] Tunjukkan kolom nilai kosong dianggap 0 dan flag "belum lengkap".

## Sesi 4 — Import Excel (≈ 1,5 menit)

- [ ] Unduh **Template Excel** (route `nilai.template`).
- [ ] Isi beberapa baris, simpan, lalu **import** pada halaman nilai.
- [ ] Tunjukkan laporan baris berhasil & baris bermasalah (jika ada).

## Sesi 5 — Detail Mahasiswa & Feedback (≈ 1 menit)

- [ ] Buka detail mahasiswa pertama (NIM `2311011001`).
- [ ] Tunjukkan rincian perhitungan: kontribusi tiap komponen → **82,50 → AB → Lulus**.
- [ ] Ketik feedback → simpan → tampil di halaman.

## Sesi 6 — Rekap & Export (≈ 1,5 menit)

- [ ] Buka **Rekap Nilai**.
- [ ] Gunakan filter mata kuliah/kelas dan pencarian NIM/nama.
- [ ] Klik **Export Excel** → tunjukkan berkas `.xlsx` terunduh.

## Sesi 7 — Uji 12 Skenario Singkat (≈ 1,5 menit)

Screen-capture hasil `php artisan test` pada terminal:

- [ ] Jalankan `php artisan test` → tampilkan **53 passed, 172 assertions**.
- [ ] (Opsional) sorot 2–3 skenario kunci: nilai 101 ditolak, bobot 90%
      ditolak, perubahan bobot mengubah nilai akhir.

---

## Tips

- Pastikan resolusi layar cukup (≥ 1280 px) dan font terbaca.
- Jangan sebut detail akun/kredensial di suara bila video akan dibagikan luas.
- Durasi total direncanakan **≈ 9 menit** agar aman di bawah batas 10 menit.