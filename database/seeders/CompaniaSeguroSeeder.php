<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // ¡Esta línea es crucial!

use App\Models\Ramo;
use App\Models\Compania;
use App\Models\Seguro;
use App\Models\CompaniaSeguro;


class CompaniaSeguroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  // database/seeders/CompaniaSeguroSeeder.php
public function run(): void
{
    $companiasRamos = [
        'Thona Seguros' => ['Vida', 'Accidentes y Enfermedades', 'Accidentes'],
        'Banorte Seguros' => ['Vida', 'Daños', 'Accidentes y Enfermedades', 'Accidentes', 'Automóviles'],
        'Insignia Lite' => ['Vida'], // Nombre actualizado
        'Alian' => ['Vida', 'Accidentes y Enfermedades'], // Nombre actualizado
        'General Seguros' => ['Vida', 'Daños', 'Accidentes', 'Automóviles'], // Nombre actualizado
        'Metlife' => ['Vida', 'Accidentes y Enfermedades'],
        'Hdi Seguros' => ['Daños', 'Accidentes y Enfermedades', 'Accidentes', 'Automóviles'], // Nombre actualizado
        'Gmx Seguros' => ['Daños'],
        'Atlas Seguros' => ['Daños', 'Accidentes', 'Automóviles'],
        'BUPA' => ['Accidentes y Enfermedades'],
        'Qualitas' => ['Automóviles'],
        'Ana Seguros' => ['Automóviles']
    ];

    foreach ($companiasRamos as $companiaNombre => $ramos) {
        $compania = Compania::where('nombre_compania', $companiaNombre)->first();

        if (!$compania) {
            $this->command->error("⚠️ Compañía no encontrada: $companiaNombre");
            continue;
        }

        $segurosIds = Seguro::whereHas('ramo', function($q) use ($ramos) {
            $q->whereIn('nombre_ramo', $ramos);
        })->pluck('id');

        $compania->seguros()->attach($segurosIds);
    }
}
}
