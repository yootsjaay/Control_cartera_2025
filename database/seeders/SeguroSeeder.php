<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ramo;
use App\Models\Compania;
use App\Models\Seguro;

class SeguroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seguros = [
            ['nombre_seguro' => 'Seguro de Vida Individual', 'ramo_id' => 1],
            ['nombre_seguro' => 'Grupo Vida', 'ramo_id' => 1],
            ['nombre_seguro' => 'Seguro de Inversion', 'ramo_id' => 1],
            ['nombre_seguro' => 'De Retiro', 'ramo_id' => 1],
            ['nombre_seguro' => 'Seguro De Daños', 'ramo_id' => 2],
            ['nombre_seguro' => 'Empresa', 'ramo_id' => 2],
            ['nombre_seguro' => 'Casa', 'ramo_id' => 2],
            ['nombre_seguro' => 'Transporte', 'ramo_id' => 2],
            ['nombre_seguro' => 'Gastos Medicos Mayores', 'ramo_id' => 3],
            ['nombre_seguro' => 'AP', 'ramo_id' => 3],
            ['nombre_seguro' => 'Accidentes Personales', 'ramo_id' => 4],
            ['nombre_seguro' => 'Escolares', 'ramo_id' => 4],
            ['nombre_seguro' => 'Automoviles', 'ramo_id' => 6],
            ['nombre_seguro' => 'Camiones', 'ramo_id' => 6],
            ['nombre_seguro' => 'Tractos', 'ramo_id' => 6],
        ];
        Seguro::insert($seguros);
    }
}
