<?php

namespace App\Http\Requests;

use App\Models\KomponenPenilaian;
use App\Models\Mahasiswa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SimpanNilaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Struktur input: nilai[<mahasiswa_id>][<komponen_id>] = <angka|kosong>.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mata_kuliah_id' => ['required', 'integer'],
            'kelas_id' => ['nullable', 'integer'],
            'nilai' => ['nullable', 'array'],
            'nilai.*' => ['array'],
            'nilai.*.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nilai.*.*.numeric' => 'Nilai harus berupa angka (0-100).',
            'nilai.*.*.min' => 'Nilai tidak boleh kurang dari 0.',
            'nilai.*.*.max' => 'Nilai tidak boleh lebih dari 100.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $mataKuliahId = (int) $this->input('mata_kuliah_id');

            $komponenIds = KomponenPenilaian::where('mata_kuliah_id', $mataKuliahId)->pluck('id')->all();
            $mahasiswaIds = Mahasiswa::whereIn('kelas_id', function ($q) use ($mataKuliahId) {
                $q->select('id')->from('kelas')->where('mata_kuliah_id', $mataKuliahId);
            })->pluck('id')->all();

            foreach ($this->input('nilai', []) as $mahasiswaId => $komponens) {
                if (! in_array((int) $mahasiswaId, $mahasiswaIds, true)) {
                    $validator->errors()->add('nilai', 'Terdapat data mahasiswa yang bukan bagian dari mata kuliah ini.');
                    continue;
                }

                foreach (array_keys((array) $komponens) as $komponenId) {
                    if (! in_array((int) $komponenId, $komponenIds, true)) {
                        $validator->errors()->add('nilai', 'Terdapat komponen penilaian yang tidak sesuai.');
                        break;
                    }
                }
            }
        });
    }
}
