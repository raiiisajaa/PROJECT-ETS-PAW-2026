<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportNilaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'berkas' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'berkas.required' => 'Silakan pilih berkas Excel yang akan diimpor.',
            'berkas.mimes' => 'Format berkas harus xlsx, xls, atau csv.',
            'berkas.max' => 'Ukuran berkas maksimal 5 MB.',
            'kelas_id.required' => 'Kelas tujuan impor wajib dipilih.',
        ];
    }
}
