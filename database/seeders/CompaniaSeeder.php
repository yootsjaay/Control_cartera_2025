<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ramo;
use App\Models\Compania;
use App\Models\Seguro;
use App\Models\CompaniaSeguro;


class CompaniaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   // database/seeders/CompaniaSeeder.php
public function run(): void
{
    // database/seeders/CompaniaSeeder.php

    $companias = [
        ['nombre_compania' => 'Thona Seguros'],
        ['nombre_compania' => 'Banorte Seguros'],
        ['nombre_compania' => 'Insignia Lite'], // Antes: "Seguros Insignia Lite"
        ['nombre_compania' => 'Alian'],         // Antes: "Alianz"
        ['nombre_compania' => 'General Seguros'],// Antes: "General de Seguros"
        ['nombre_compania' => 'Metlife'],
        ['nombre_compania' => 'Hdi Seguros'],   // Antes: "HDI Seguros"
        ['nombre_compania' => 'Gmx Seguros'],
        ['nombre_compania' => 'Atlas Seguros'],
        ['nombre_compania' => 'BUPA'],
        ['nombre_compania' => 'Qualitas'],
        ['nombre_compania' => 'Ana Seguros']
    ];

    Compania::insert($companias);
}
}
