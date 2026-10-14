# Mekanisme Perubahan Bobot

Dokumen ini menjelaskan **bagaimana dosen mengubah bobot komponen penilaian
dan dampaknya terhadap nilai akhir mahasiswa**, beserta aturan validasi yang
memastikan total bobot selalu 100%.

---

## 1. Bagaimana Bobot Disimpan

UI menerima input bobot sebagai **angka utuh persen** (20 untuk 20%), sesuai
kebiasaan dosen. Di sisi server nilai itu dikonversi menjadi **desimal**
sebelum disimpan ke kolom `komponen_penilaian.bobot DECIMAL(3,2)`:

```
bobot_tersimpan = round(inputPersen / 100, 2)
   20  → 0,20
   25  → 0,25
   100 → 1,00   (satu komponen = 100%)
```

Cara ini membuat total tersimpan selalu bandingan yang bersih (mis. `1,00`)
tanpa kehilangan presisi desimal biner.

---

## 2. Validasi Total Bobot = 100%

Validasi dilakukan **dua lapis**:

**a. Klien (JavaScript, `public/js/app.js`)**
Form kelola komponen menampilkan **total bobot secara langsung** (dalam %).
Tombol simpan terkunci dan muncul pesan bila total ≠ 100.

**b. Server (Form Request `SimpanKomponenRequest` → `NilaiService`)** — keamanan
utama, tidak bisa dilewati:

1. Setiap baris komponen dikonversi `bobot` (persen) → desimal.
2. `NilaiService::totalBobotValid()` menjumlahkan seluruh bobot dan memeriksa
   `abs(total − 1,00) < 0,001`.
3. Jika total ≠ 1,00, request ditolak dengan error `komponens` (HTTP redirect
   kembali disertai pesan), **tidak ada satu baris pun** yang disimpan —
   perubahan bobot bersifat atomik per mata kuliah.

Skenario yang diuji:

| Total bobot yang dikirim | Keputusan |
| --- | --- |
| 20+20+25+35 = **100%** | ✅ diterima |
| 20+20+25+25 = **90%** | ❌ ditolak |
| 30+30+25+25 = **110%** | ❌ ditolak |
| 50+30 = **80%** | ❌ ditolak |
| 33,33+33,33+33,34 = **100%** | ✅ diterima (banding desimal bersih) |

---

## 3. Pengelolaan Baris Komponen

Pada form, setiap baris komponen membawa `id`:

- **`id` lama** (sudah ada di DB) → baris tersebut **diperbarui** (nama/bobot).
- **`id` kosong (`null`)** → baris baru dibuat.
- **Baris lama yang tidak dikirim** → **dihapus** (beserta nilai-nilai
  terkaitnya, mengikuti relasi cascade).

Validasi tambahan: nama komponen unik per mata kuliah, jumlah minimal 1 dan
maksimal 20 komponen, bobot per komponen 0–100%.

---

## 4. Dampak ke Nilai Akhir — Perhitungan Otomatis

Nilai mahasiswa disimpan **per komponen** (tabel `nilai`), bukan sebagai
"nilai akhir" yang beku. Nilai akhir dihitung setiap kali ditampilkan:

```
Nilai Akhir = Σ ( nilai_mahasiswa_untuk_komponen_i × bobot_komponen_i )
```

Akibatnya, **perubahan bobot otomatis mengubah seluruh nilai akhir** tanpa
perlu menyentuh satu pun baris `nilai`, dan tanpa proses batch/massal:

```
Bobot lama (Tugas1 20%, Tugas2 20%, UTS 25%, UAS 35%)
   Mahasiswa dengan nilai T1=100, T2=0, UTS=0, UAS=0
   Nilai akhir = 100×0,20 + 0 + 0 + 0 = 20,0

Bobot baru (Tugas1 40%, Tugas2 10%, UTS 20%, UAS 30%)  — total tetap 100%
   Nilai yang sama → Nilai akhir = 100×0,40 = 40,0
```

Contoh di atas teruji di `tests/Feature/KomponenPenilaianTest.php`
(`test_perubahan_bobot_mengubah_nilai_akhir_mahasiswa`, skenario 10):
nilai akhir berubah dari `20,0` menjadi `40,0`.

---

## 5. Alur Perubahan Bobot (End-to-End)

```
Dosen membuka /mata-kuliah/{id}/komponen
   ├─ Masukkan nama & bobot per komponen (format persen)
   ├─ JS menampilkan total bobot secara real-time
   ├─ Kirim (POST komponen.simpan)
   │     ├─ SimpanKomponenRequest memvalidasi (total = 100%, unik nama, dll)
   │     └─ NilaiService::totalBobotValid() mengonfirmasi
   ├─ Baris lama diperbarui / baru dibuat / yang dihapus dihapus
   └─ Redirect ke halaman komponen + notifikasi sukses
        └─ Semua nilai akhir otomatis memakai bobot terbaru
             (halaman nilai, detail mahasiswa, rekap, export Excel)
```

---

## 6. Alasan Desain ini (Kenapa Bukan Menyimpan Nilai Akhir)

1. **Nilai akhir selalu up-to-date** — tidak ada nilai basi setelah ubah bobot.
2. **Tidak perlu job/kron job** merekalkulasi basis data.
3. **Satu sumber kebenaran** — `NilaiService` dipakai layar, detail, dan Excel.
4. **Audit koreksi nilai** — nilai asli per komponen tetap utuh di DB.

Batas-batas yang disepakati: total bobot harus tepat 100% (toleransi 0,001),
bobot disimpan 2 desimal, dan perhitungan dibulatkan 2 desimal.