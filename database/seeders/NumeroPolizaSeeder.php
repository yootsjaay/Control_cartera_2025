<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NumerosPoliza;

class NumeroPolizaSeeder extends Seeder
{
    public function run()
    {
        $numeros = [
            '173-13491-1',
            '1001597',
            '1003328',
            '1023737',
            '1003989',
            '07000002',
            '07000075',
            '173-4155',
            '173-3753',
            '173-3896',
            '173-3989',
            '173-3634'
        ];

        foreach ($numeros as $numero) {
            NumerosPoliza::firstOrCreate([
                'numero_poliza' => str_replace(' ', '', $numero), // limpiamos si tiene espacios
            ]);
        }
    }
}
