<?php

namespace Database\Factories;

use App\Models\MasterSekolah;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterSekolahFactory extends Factory
{
    protected $model = MasterSekolah::class;

    public function definition(): array
    {
        return [
            'nisn' => fake()->unique()->numerify(str_repeat('#', 10)),
            'nama_sekolah' => fake()->company() . ' ' . fake()->randomElement(['SD', 'SMP', 'SMA', 'SMK']),
            'alamat' => fake()->address(),
        ];
    }
}
