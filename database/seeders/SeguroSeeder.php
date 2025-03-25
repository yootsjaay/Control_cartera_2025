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
         // Obtener ramos
         $vida = Ramo::where('nombre_ramo', 'Vida')->first();
         $danos = Ramo::where('nombre_ramo', 'Daños')->first();
         $salud = Ramo::where('nombre_ramo', 'Accidentes y Enfermedades')->first();
         $accidentes = Ramo::where('nombre_ramo', 'Accidentes')->first();
         $automoviles = Ramo::where('nombre_ramo', 'Automóviles')->first();
 
        // Seguros de Vida
        Seguro::create([
            'nombre_seguro' => 'Seguro de Vida Individual',
            'ramo_id' => $vida->id
        ]);

        Seguro::create([
            'nombre_seguro' => 'Grupo Vida',
            'ramo_id' => $vida->id
        ]);

        Seguro::create([
            'nombre_seguro' => 'Seguro de Inversión',
            'ramo_id' => $vida->id
        ]);

        Seguro::create([
            'nombre_seguro' => 'Seguro de Retiro',
            'ramo_id' => $vida->id
        ]);

        // Seguros de Daños
        Seguro::create([
            'nombre_seguro' => 'Seguro de Daños a Empresa',
            'ramo_id' => $danos->id
        ]);

        Seguro::create([
            'nombre_seguro' => 'Seguro de Daños a Casa',
            'ramo_id' => $danos->id
        ]);

        Seguro::create([
            'nombre_seguro' => 'Seguro de Transporte',
            'ramo_id' => $danos->id
        ]);

        // Seguros de Salud
        Seguro::create([
            'nombre_seguro' => 'Gastos Médicos Mayores',
            'ramo_id' => $salud->id
        ]);

        // Seguros de Accidentes
        Seguro::create([
            'nombre_seguro' => 'Accidentes Personales',
            'ramo_id' => $accidentes->id
        ]);

        Seguro::create([
            'nombre_seguro' => 'Accidentes Escolares',
            'ramo_id' => $accidentes->id
        ]);

        // Seguros de Automóviles
        Seguro::create([
            'nombre_seguro' => 'Seguro para Autos Pickup',
            'ramo_id' => $automoviles->id
        ]);

        Seguro::create([
            'nombre_seguro' => 'Seguro para Camiones',
            'ramo_id' => $automoviles->id
        ]);

        Seguro::create([
            'nombre_seguro' => 'Seguro para Tractos',
            'ramo_id' => $automoviles->id
        ]);
    }
}
