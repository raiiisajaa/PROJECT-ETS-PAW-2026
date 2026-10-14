<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MataKuliah>
 */
class MataKuliahFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kode' => 'MK'.Str::upper(Str::random(3)).$this->faker->unique()->numberBetween(100, 999),
            'nama' => $this->faker->words(3, true),
            'semester' => $this->faker->numberBetween(1, 8),
            'sks' => $this->faker->numberBetween(2, 4),
        ];
    }
}
