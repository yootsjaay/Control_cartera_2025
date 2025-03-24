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
    public function run(): void
    {
        $companias = [
            ['nombre_compania' => 'Thona Seguros'],
            ['nombre_compania' => 'Banorte Seguros'],
            ['nombre_compania' => 'Insignia Lite'],
            ['nombre_compania' => 'Alian'],
            ['nombre_compania' => 'General Seguros'],
            ['nombre_compania' => 'Metlife'],
            ['nombre_compania' => 'Hdi Seguros'],
            ['nombre_compania' => 'Gmx Seguros'],
            ['nombre_compania' => 'Atlas Seguros'],
            ['nombre_compania' => 'BUPA Seguros'],
            ['nombre_compania' => 'Qualitas Seguros'],
            ['nombre_compania' => 'Ana Seguros'],

           
        ];
        Compania::insert($companias);
    }
}
