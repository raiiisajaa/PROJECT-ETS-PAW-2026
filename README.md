# Sistem Penilaian Tugas Mahasiswa (ETS PAW — Soal 3)

Aplikasi web **Laravel 10** untuk mengelola penilaian tugas mahasiswa oleh dosen,
sesuai dokumen PRD *"Sistem Penilaian Tugas Mahasiswa"* (ETS PAW Soal 3).

Aplikasi mengikuti arsitektur **MVC** dengan **Eloquent ORM**, **Form Request**
(validasi server), **Migration**, **Seeder**, **Laravel Excel** (import/export),
serta **layanan `NilaiService`** sebagai satu sumber kebenaran perhitungan
nilai akhir, grade, dan status kelulusan.

---

## Fitur (lingkup PRD)

| Modul | Fitur |
| --- | --- |
| Autentikasi | Login & logout dosen (satu peran, **dosen**) |
| Dashboard | Ringkasan mata kuliah yang diampu |
| Komponen & Bobot | Kelola komponen penilaian + bobot (desimal), pengecekan total = 100% |
| Input Nilai | Input manual per mahasiswa/keyboard + **import Excel** (template bawaan) |
| Pencarian | Cari mahasiswa/nilai (halaman nilai & rekap) |
| Detail Mahasiswa | Identitas, rincian perhitungan per komponen, grade, status, feedback dosen |
| Rekap Nilai | Tabel rekap nilai akhir + filter *mata kuliah/kelas/pencarian* + **export Excel** |
| Validasi berlapis | JavaScript (klien) + Form Request (server) |
| Basis data | Migration + Seeder data contoh |

Di luar lingkup PRD (tidak dikerjakan): portal mahasiswa, manajemen pengguna,
integrasi kampus, email/WA, ekspor PDF, dan grafik lanjutan.

---

## Teknologi

- PHP **8.1** (Laragon), Composer 2
- Laravel **10.50** (MVC, Eloquent, Form Request, Migration, Seeder)
- MySQL **8.0** (dijalankan; otomatis dialihkan ke SQLite saat pengujian)
- `maatwebsite/excel 3.1` (import nilai & export rekap/template)
- Blade + HTML5/CSS3/JavaScript (aset statis di `public/`, tanpa npm)

---

## Persyaratan

- PHP ≥ 8.1 dengan ekstensi `pdo`, `pdo_mysql`, `pdo_sqlite`, `zip`, `gd` (untuk laravel-excel)
- Composer ≥ 2.x
- MySQL ≥ 5.7 / 8.x

---

## Cara Pasang & Menjalankan (Windows/Laragon)

1. **Ekstrak/letakkan project** di folder web server (contoh: `C:\laragon\www\penilaian-mahasiswa`).

2. **Instal dependensi Composer**:

   ```bash
   composer install
   ```

3. **Buat file environment** dari contoh:

   ```bash
   copy .env.example .env
   ```

   Sesuaikan isi `.env` untuk koneksi MySQL:

   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=penilaian_mahasiswa
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Buat `APP_KEY`**:

   ```bash
   php artisan key:generate
   ```

5. **Migrasi + seed data contoh** (seluruh tabel beserta data dosen, mata kuliah
   , kelas, mahasiswa, komponen, nilai, dan feedback dibuat otomatis):

   ```bash
   php artisan migrate:fresh --seed
   ```

   > Skema SQL manual yang setara tersedia di [`database/schema.sql`](database/schema.sql).

6. **Jalankan server**:

   ```bash
   php artisan serve
   ```

   Buka `http://127.0.0.1:8000`.

---

## Akun Demo

| Peran | Email | Password |
| --- | --- | --- |
| Dosen | `raiiisaja@gmail.com` | `password` |

Data contoh: **3 mata kuliah × 2 kelas × 10 mahasiswa** dengan komponen standar:

| Komponen | Bobot |
| --- | --- |
| Tugas 1 | 20% (`0,20`) |
| Tugas 2 | 20% (`0,20`) |
| UTS | 25% (`0,25`) |
| UAS | 35% (`0,35`) |

Mahasiswa pertama (NIM `2311011001`) diisi nilai **80/90/75/85** → nilai akhir
**82,50** → grade **AB** → **Lulus** (contoh perhitungan PRD).

---

## Aturan Bisnis Utama

- **Rumus nilai akhir:** `Nilai Akhir = Σ (Nilai Komponen × Bobot Komponen)`, hasil dibulatkan **2 desimal**.
- **Bobot:** desimal (`20% = 0,20`), total komponen **harus tepat 100%** (1,00).
- **Nilai komponen:** rentang **0–100**.
- **Grade** (dibandingkan terhadap batas bawah setelah pembulatan 2 desimal):

  | Nilai akhir | Grade | Status |
  | --- | --- | --- |
  | ≥ 85,00 | A | Lulus |
  | ≥ 80,00 | AB | Lulus |
  | ≥ 70,00 | B | Lulus |
  | ≥ 65,00 | BC | Lulus |
  | ≥ 55,00 | C | Lulus |
  | ≥ 40,00 | D | Tidak Lulus |
  | < 40,00 | E | Tidak Lulus |

Lihat [`docs/mekanisme-perhitungan-nilai.md`](docs/mekanisme-perhitungan-nilai.md) untuk penjelasan lengkap.

---

## Route Utama

| Method | URI | Nama route | Keterangan |
| --- | --- | --- | --- |
| GET | `/login` | `login` | Halaman masuk |
| POST | `/login` | `login` | Proses masuk |
| POST | `/logout` | `logout` | Keluar |
| GET | `/dashboard` | `dashboard` | Dashboard |
| GET | `/mata-kuliah/{mataKuliah}/komponen` | `komponen.index` | Kelola komponen & bobot |
| POST | `/mata-kuliah/{mataKuliah}/komponen` | `komponen.simpan` | Simpan komponen |
| GET | `/mata-kuliah/{mataKuliah}/nilai` | `nilai.index` | Input nilai (manual) |
| POST | `/mata-kuliah/{mataKuliah}/nilai` | `nilai.simpan` | Simpan nilai |
| POST | `/mata-kuliah/{mataKuliah}/nilai/import` | `nilai.import` | Import nilai Excel |
| GET | `/mata-kuliah/{mataKuliah}/nilai/template` | `nilai.template` | Unduh template Excel |
| GET | `/mahasiswa/{mahasiswa}/penilaian` | `nilai.detail` | Detail penilaian mahasiswa |
| POST | `/mahasiswa/{mahasiswa}/penilaian` | `nilai.feedback` | Simpan feedback |
| GET | `/rekap` | `rekap.index` | Rekap nilai + filter |
| GET | `/rekap/export` | `rekap.export` | Export rekap ke Excel |
| GET | `/profil` | `profil` | Profil dosen |
| GET | `/mahasiswa` | `mahasiswa.index` | Data mahasiswa |

---

## Menjalankan Pengujian (12 Skenario + FR)

Pengujian berjalan pada **SQLite in-memory** sehingga data MySQL tidak terganggu:

```bash
php artisan test
```

Hasil: **53 test, 172 assertions — semua lulus** (termasuk 12 skenario pengujian PRD).

Peta 12 skenario → test di [`docs/hasil-pengujian.md`](docs/hasil-pengujian.md).

---

## Struktur Project (ringkas)

```
app/
  Http/Controllers/        Controller per modul (dashboard, komponen, nilai, rekap, …)
  Http/Requests/           Form Request (validasi server)
  Imports/NilaiImport.php  Import nilai Excel
  Exports/                 RekapExport, TemplateNilaiExport
  Models/                  User, MataKuliah, Kelas, Mahasiswa, KomponenPenilaian, Nilai, Feedback
  Services/NilaiService.php  Perhitungan nilai akhir, grade & status
database/
  migrations/              7 tabel (users + 6 domain)
  seeders/DatabaseSeeder.php  Data contoh
  schema.sql               Skema MySQL manual
  factories/               Factory untuk pengujian
routes/web.php             Seluruh route aplikasi
resources/views/           Blade (layout, login, dashboard, komponen, nilai, rekap, mahasiswa, profil)
public/css/app.css, public/js/app.js   Aset statis
tests/                     53 pengujian (unit + fitur) untuk 12 skenario PRD
docs/                      ERD, mekanisme perhitungan/bobot, hasil pengujian, screenshot
```

---

## Screenshot Halaman

Tersedia di [`docs/screenshots/`](docs/screenshots/):

1. [`01-login.png`](docs/screenshots/01-login.png) — Halaman login
2. [`02-dashboard.png`](docs/screenshots/02-dashboard.png) — Dashboard
3. [`03-komponen.png`](docs/screenshots/03-komponen.png) — Komponen & bobot
4. [`04-nilai.png`](docs/screenshots/04-nilai.png) — Input nilai
5. [`05-mahasiswa.png`](docs/screenshots/05-mahasiswa.png) — Data mahasiswa
6. [`06-rekap.png`](docs/screenshots/06-rekap.png) — Rekap nilai
7. [`07-detail.png`](docs/screenshots/07-detail.png) — Detail penilaian + feedback

---

## Deliverables PRD

- [x] Source code + instruksi instalasi (README ini)
- [x] Migration + Seeder (folder `database/`) + `database/schema.sql`
- [x] ERD — [`docs/ERD.md`](docs/ERD.md)
- [x] Dokumentasi mekanisme perhitungan — [`docs/mekanisme-perhitungan-nilai.md`](docs/mekanisme-perhitungan-nilai.md)
- [x] Dokumentasi mekanisme perubahan bobot — [`docs/mekanisme-perubahan-bobot.md`](docs/mekanisme-perubahan-bobot.md)
- [x] Screenshot 6+ halaman — [`docs/screenshots/`](docs/screenshots/)
- [x] Hasil 12 skenario pengujian — [`docs/hasil-pengujian.md`](docs/hasil-pengujian.md)
- [ ] Video demo (≤ 10 menit) — **perlu direkam mandiri** (mis. Loom/OBS), karena tidak dapat direkam otomatis oleh agen. Alur demo disarankan di [`docs/demo-checklist.md`](docs/demo-checklist.md)