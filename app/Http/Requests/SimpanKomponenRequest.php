<?php

namespace App\Http\Requests;

use App\Services\NilaiService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SimpanKomponenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Bobot dikirim dari form dalam bentuk persen (contoh 20 untuk 20%).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'komponens' => ['required', 'array', 'min:1'],
            'komponens.*.id' => ['nullable', 'integer'],
            'komponens.*.nama' => ['required', 'string', 'max:100'],
            'komponens.*.bobot' => ['required', 'numeric', 'gt:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'komponens.required' => 'Minimal harus ada satu komponen penilaian.',
            'komponens.*.nama.required' => 'Nama komponen wajib diisi.',
            'komponens.*.bobot.required' => 'Bobot komponen wajib diisi.',
            'komponens.*.bobot.numeric' => 'Bobot harus berupa angka.',
            'komponens.*.bobot.gt' => 'Bobot harus lebih besar dari 0.',
            'komponens.*.bobot.max' => 'Bobot maksimal 100%.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $komponens = collect($this->input('komponens', []));

            if ($validator->errors()->has('komponens')) {
                return;
            }

            // Nama komponen tidak boleh duplikat dalam satu mata kuliah.
            $nama = $komponens->pluck('nama')->map(fn ($n) => mb_strtolower(trim((string) $n)));
            if ($nama->count() !== $nama->unique()->count()) {
                $validator->errors()->add('komponens', 'Nama komponen tidak boleh sama.');
            }

            // Total bobot harus tepat 100% (FR-13, FR-14, skenario 5-7).
            if (! app(NilaiService::class)->totalBobotValid(
                $komponens->map(fn ($k) => ['bobot' => round(((float) ($k['bobot'] ?? 0)) / 100, 2)])
            )) {
                $validator->errors()->add('komponens', 'Total seluruh bobot harus 100%.');
            }
        });
    }
}
