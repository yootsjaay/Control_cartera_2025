<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ramo;
use App\Models\Seguro;
use App\Models\Compania;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Datos de ramos, seguros y compañías
        $ramos = [
            'Vida' => [
                'seguros' => ['Seguro de Vida Individual', 'Grupo vida', 'Seguro de inversión', 'De retiro'],
                'companias' => ['Thona Seguros', 'Banorte Seguros', 'Insignia Life', 'Allianz', 'Metlife', 'General de Seguros']
            ],
            'Daños' => [
                'seguros' => ['Seguro de Daños empresa', 'Casa', 'Transporte','Embarcaciones','Rc Agentes','Rc Colegios','Rc Estancias Infantiles' ,'Maquinaria'],
                'companias' => ['HDI Seguros', 'Banorte Seguros', 'Gmx Seguros', 'General de Seguros', 'Atlas Seguros']
            ],
            'Accidentes y enfermedades' => [
                'seguros' => ['Gastos Médicos Mayores', 'Accidentes Personales', 'Accidentes Personales Escolares'],
                'companias' => ['HDI Seguros', 'Banorte Seguros', 'Metlife', 'Alianz', 'BUPA', 'Thona Seguros', 'General de Seguros', 'Atlas Seguros']
            ],
            'Automóviles' => [
                'seguros' => ['Autos pickup', 'Camiones', 'Tractos'],
                'companias' => ['Banorte Seguros', 'General de Seguros', 'Atlas Seguros', 'Qualitas', 'Ana Seguros', 'HDI Seguros']
            ]
        ];

        // Crear compañías (si no existen ya)
        $companiasInstances = [];
        foreach (array_unique(array_merge(...array_column($ramos, 'companias'))) as $nombre) {
            $companiasInstances[$nombre] = Compania::firstOrCreate(['nombre' => $nombre]);
        }

        // Crear ramos, seguros y asignar compañías correctamente
        foreach ($ramos as $ramoNombre => $data) {
            // Crear ramo solo si no existe
            $ramo = Ramo::firstOrCreate(['nombre' => $ramoNombre]);

            foreach ($data['seguros'] as $seguroNombre) {
                // Crear seguro
                $seguro = Seguro::firstOrCreate([
                    'nombre' => $seguroNombre,
                    'ramo_id' => $ramo->id
                ]);

                // Asignar compañías específicas a cada seguro
                $companiaIds = array_map(fn($nombre) => $companiasInstances[$nombre]->id, array_unique($data['companias']));
                $seguro->companias()->syncWithoutDetaching($companiaIds); // Evita duplicados
            }
        }
    }
}
