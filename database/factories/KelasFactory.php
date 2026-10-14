<?php

namespace Database\Factories;

use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kelas>
 */
class KelasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mata_kuliah_id' => MataKuliah::factory(),
            'nama_kelas' => 'Kelas '.$this->faker->unique()->randomLetter(),
        ];
    }
}
