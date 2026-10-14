<?php

namespace Database\Factories;

use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kelas_id' => Kelas::factory(),
            'nim' => (string) $this->faker->unique()->numerify('##########'),
            'nama' => $this->faker->name(),
            'program_studi' => 'Teknik Informatika',
        ];
    }
}
