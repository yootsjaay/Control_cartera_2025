<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ramo;
class RamoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ramos = [
            ['nombre_ramo' => 'Vida'],
            ['nombre_ramo' => 'Daños'],
            ['nombre_ramo' => 'Salud'],
            ['nombre_ramo' => 'Accidentes'],
            ['nombre_ramo' => 'Accidentes y Enfermedades'],
            ['nombre_ramo' => 'Automóviles']
        ];
        Ramo::insert($ramos);
    }
}