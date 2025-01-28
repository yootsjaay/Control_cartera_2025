<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CompaniasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companias')->insert([
            [
                'nombre' => 'Qualitas',
                'slug' => 'qualitas_seguros',
                'clase' => 'App\Services\QualitasSeguroService',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ]);
    }
}
