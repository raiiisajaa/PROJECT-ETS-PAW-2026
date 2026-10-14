<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MahasiswaRequest extends FormRequest
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
        $mahasiswaId = $this->route('mahasiswa')?->id;

        return [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'nim' => ['required', 'string', 'max:20', Rule::unique('mahasiswa', 'nim')->ignore($mahasiswaId)],
            'nama' => ['required', 'string', 'max:100'],
            'program_studi' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'nama.required' => 'Nama mahasiswa wajib diisi.',
            'program_studi.required' => 'Program studi wajib diisi.',
        ];
    }
}
