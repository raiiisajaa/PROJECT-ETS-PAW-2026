# Mekanisme Perhitungan Nilai

Dokumen ini menjelaskan **bagaimana sistem menghitung nilai akhir, grade, dan
status kelulusan**. Implementasinya terpusat di
`app/Services/NilaiService.php` (single source of truth) dan dipakai oleh
halaman input nilai, detail mahasiswa, rekap, serta export Excel.

---

## 1. Asumsi & Sumber Data

| Konsep | Nilai |
| --- | --- |
| Nilai komponen | angka **0–100**, disimpan `DECIMAL(5,2)` di tabel `nilai` |
| Bobot komponen | **desimal**, disimpan `DECIMAL(3,2)` di tabel `komponen_penilaian` (`0,20` = 20%) |
| Total bobot | **tepat 1,00 (100%)** |
| Nilai akhir | dihitung, bukan disimpan; dibulatkan **2 angka di belakang koma** |

Karena nilai akhir tidak disimpan, setiap perubahan data (nilai, bobot,
komponen) langsung tercermin pada hasil perhitungan berikutnya.

---

## 2. Rumus Nilai Akhir

```
                n
Nilai Akhir =  Σ  ( Nilaiᵢ × Bobotᵢ )        i = 1..n (n = jumlah komponen)
               i=1
```

Hasil akhir dibulatkan ke **2 desimal** dengan `round($total, 2)`.

### Contoh per PRD (teruji di `tests/Unit/NilaiServiceTest.php`)

| Komponen | Nilai | Bobot | Kontribusi (Nilai × Bobot) |
| --- | ---: | ---: | ---: |
| Tugas 1 | 80 | 0,20 | 16,00 |
| Tugas 2 | 90 | 0,20 | 18,00 |
| UTS | 75 | 0,25 | 18,75 |
| UAS | 85 | 0,35 | 29,75 |
| **Jumlah** | | **1,00** | **82,50** |

**Nilai Akhir = 82,50 → Grade AB → Lulus.**

Verifikasi nyata pada data seed (NIM `2311011001`):

```
contoh-2311011001 akhir=82.5 grade=AB Lulus
```

---

## 3. Pembulatan

Pembulatan dilakukan **2 desimal** dengan `round()` (mode default PHP:

half up):

- Matematika murni per komponen: `round(nilai × bobot, 2)`.
- Total: `round(Σ kontribusi, 2)`.

Akibatnya pembandingan grade selalu memakai nilai yang **sudah dibulatkan**:

- `85,00 → A` tetapi `84,99 → AB`
- `55,00 → C` tetapi `54,99 → D`

---

## 4. Pemetaan Grade & Status

Tabel grade di `NilaiService::GRADE_RANGES` diurutkan **menurun** menurut
batas bawah; grade dipilih pada rentang pertama yang `nilai ≥ min`:

| Batas bawah | Batas atas | Grade | Status |
| ---: | ---: | :---: | :---: |
| 85,00 | 100,00 | **A** | Lulus |
| 80,00 | 84,99 | **AB** | Lulus |
| 70,00 | 79,99 | **B** | Lulus |
| 65,00 | 69,99 | **BC** | Lulus |
| 55,00 | 64,99 | **C** | Lulus |
| 40,00 | 54,99 | **D** | Tidak Lulus |
| 0,00 | 39,99 | **E** | Tidak Lulus |

Status kelulusan: **A, AB, B, BC, C → "Lulus"**; **D, E → "Tidak Lulus"**
(konstanta `GRADE_LULUS`).

Contoh pembandingan yang sudah diuji:

| Nilai akhir | Grade | Status |
| ---: | :---: | :---: |
| 100,00 | A | Lulus |
| 85,00 | A | Lulus |
| 84,50 | AB | Lulus |
| 70,00 | B | Lulus |
| 65,00 | BC | Lulus |
| 55,00 | C | Lulus |
| 54,75 | D | Tidak Lulus |
| 49,00 | D | Tidak Lulus |
| 40,00 | D | Tidak Lulus |
| 39,99 | E | Tidak Lulus |

---

## 5. Kelengkapan Data

`NilaiService::ringkasan()` juga menghasilkan bendera `lengkap`:

- `lengkap = true` — semua komponen sudah terisi nilai.
- `lengkap = false` — ada komponen kosong; komponen kosong **dihitung 0**
  sehingga nilai akhir tetap terhitung, dan UI menandai data belum lengkap.

---

## 6. Alur Call dari Kontroler

```
Kontroler (nilai.index / nilai.detail / rekap.index / rekap.export)
    └─ NilaiService::ringkasan($mahasiswa, ?$komponens)
          ├─ ambil komponen (urut id) dari mata kuliah mahasiswa
          ├─ untuk tiap komponen:
          │     nilai   = mahasiswa.nilaiKomponen(komponen.id) ?? 0
          │     bobot   = komponen.bobot (desimal)
          │     kontribusi = round(nilai × bobot, 2)
          ├─ total = round(Σ kontribusi, 2)        → nilai akhir
          ├─ grade  = tentukanGrade(total)
          └─ status = tentukanStatus(total)
```

Hasil `ringkasan()` mencakup: `nilai_akhir`, `grade`, `status`, `lengkap`,
dan `detail` (rincian per komponen: nama, nilai, bobot, bobot persen,
kontribusi) untuk ditampilkan di halaman detail.

---

## 7. Konsistensi dengan Excel Rekap

Export Excel (`app/Exports/RekapExport.php`) memakai jalur perhitungan yang
sama (`NilaiService`) sehingga angka di layar, di detail mahasiswa, dan di
berkas Excel selalu identik — satu sumber kebenaran.

---

## 8. Pengujian yang Menjamin Mekanisme Ini

- `tests/Unit/NilaiServiceTest.php` — rumus contoh PRD (82,50 → AB), kontribusi
  tiap komponen, batas-batas grade, dan validasi total bobot.
- `tests/Feature/InputNilaiTest.php` — skenario 85 → A dan 49 → D lewat alur
  HTTP nyata.
- `tests/Feature/RekapNilaiTest.php` & `DetailPenilaianTest.php` — angka
  `82,50`/`AB` muncul pada halaman rekap dan detail.

Lihat [`hasil-pengujian.md`](hasil-pengujian.md) untuk hasil lengkap.