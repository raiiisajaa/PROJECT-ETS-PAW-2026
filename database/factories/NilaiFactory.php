<?php

namespace Database\Factories;

use App\Models\KomponenPenilaian;
use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Nilai>
 */
class NilaiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'komponen_id' => KomponenPenilaian::factory(),
            'nilai' => $this->faker->numberBetween(0, 100),
        ];
    }
}
