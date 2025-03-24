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
    public function run(): void
    {
        $relaciones = [
            // Thona Seguros (1) - Vida y Daños
            ['compania_id' => 1, 'seguro_id' => 1], // Seguro de Vida Individual
            ['compania_id' => 1, 'seguro_id' => 2], // Grupo Vida
            ['compania_id' => 1, 'seguro_id' => 5], // Seguro de Daños

            // Banorte Seguros (2) - Vida y Automóviles
            ['compania_id' => 2, 'seguro_id' => 1], // Seguro de Vida Individual
            ['compania_id' => 2, 'seguro_id' => 13], // Automóviles
            ['compania_id' => 2, 'seguro_id' => 14], // Camiones

            // Insignia Lite (3) - Salud
            ['compania_id' => 3, 'seguro_id' => 9], // Gastos Médicos Mayores

            // Alian (4) - Daños
            ['compania_id' => 4, 'seguro_id' => 6], // Empresa
            ['compania_id' => 4, 'seguro_id' => 7], // Casa

            // General Seguros (5) - Automóviles y Daños
            ['compania_id' => 5, 'seguro_id' => 13], // Automóviles
            ['compania_id' => 5, 'seguro_id' => 5], // Seguro de Daños

            // Metlife (6) - Vida y Salud
            ['compania_id' => 6, 'seguro_id' => 1], // Seguro de Vida Individual
            ['compania_id' => 6, 'seguro_id' => 3], // Seguro de Inversión
            ['compania_id' => 6, 'seguro_id' => 9], // Gastos Médicos Mayores

            // Hdi Seguros (7) - Automóviles
            ['compania_id' => 7, 'seguro_id' => 13], // Automóviles
            ['compania_id' => 7, 'seguro_id' => 15], // Tractos

            // Gmx Seguros (8) - Salud y Accidentes
            ['compania_id' => 8, 'seguro_id' => 9], // Gastos Médicos Mayores
            ['compania_id' => 8, 'seguro_id' => 11], // Accidentes Personales

            // Atlas Seguros (9) - Daños
            ['compania_id' => 9, 'seguro_id' => 7], // Casa
            ['compania_id' => 9, 'seguro_id' => 8], // Transporte

            // BUPA Seguros (10) - Salud
            ['compania_id' => 10, 'seguro_id' => 9], // Gastos Médicos Mayores
            ['compania_id' => 10, 'seguro_id' => 10], // AP

            // Qualitas Seguros (11) - Automóviles
            ['compania_id' => 11, 'seguro_id' => 13], // Automóviles
            ['compania_id' => 11, 'seguro_id' => 14], // Camiones
            ['compania_id' => 11, 'seguro_id' => 15], // Tractos

            // Ana Seguros (12) - Accidentes y Vida
            ['compania_id' => 12, 'seguro_id' => 11], // Accidentes Personales
            ['compania_id' => 12, 'seguro_id' => 2], // Grupo Vida
        ];

        DB::table('compania_seguro')->insert($relaciones);
    
    }
}
