<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feedback>
 */
class FeedbackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'mata_kuliah_id' => MataKuliah::factory(),
            'isi' => $this->faker->sentence(),
        ];
    }
}
