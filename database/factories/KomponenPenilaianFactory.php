<?php

namespace Database\Factories;

use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KomponenPenilaian>
 */
class KomponenPenilaianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mata_kuliah_id' => MataKuliah::factory(),
            'nama' => 'Komponen '.$this->faker->unique()->word(),
            'bobot' => 0.25,
        ];
    }
}
