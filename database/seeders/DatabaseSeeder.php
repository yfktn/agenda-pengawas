<?php

namespace Database\Seeders;

use App\Models\MasterSekolah;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $sekolahs = MasterSekolah::factory()->count(20)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'Admin',
        ]);

        $sekolahs->take(10)->each(function ($sekolah) {
            User::factory()->create([
                'name' => "Operator {$sekolah->nama_sekolah}",
                'email' => "operator{$sekolah->id}@example.com",
                'role' => 'OperatorSekolah',
                'sekolah_id' => $sekolah->id,
            ]);
        });

        $sekolahs->take(10)->chunk(2)->each(function ($chunk, $index) {
            $pengawas = User::factory()->create([
                'name' => 'Pengawas ' . ($index + 1),
                'email' => 'pengawas' . ($index + 1) . '@example.com',
                'role' => 'Pengawas',
            ]);

            $pengawas->penugasanSekolah()->attach($chunk->pluck('id'));
        });
    }
}
