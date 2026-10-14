<?php

namespace App\Exports;

use App\Models\MataKuliah;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template Excel untuk impor nilai (kolom mengikuti komponen mata kuliah).
 */
class TemplateNilaiExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(protected MataKuliah $mataKuliah)
    {
    }

    public function array(): array
    {
        // Satu baris contoh agar dosen paham formatnya.
        $contoh = ['2210000001', 'Nama Mahasiswa Contoh'];
        foreach ($this->mataKuliah->komponens as $komponen) {
            $contoh[] = 80;
        }

        return [$contoh];
    }

    public function headings(): array
    {
        $headings = ['nim', 'nama'];
        foreach ($this->mataKuliah->komponens as $komponen) {
            $headings[] = $komponen->nama;
        }

        return $headings;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
