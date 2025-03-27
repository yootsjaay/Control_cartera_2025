<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Ramo;
use App\Models\Seguro;
use App\Models\Compania;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
       /* // Crear permisos
        $permisos = [
            'ver usuarios', 'crear usuarios', 'editar usuarios', 'eliminar usuarios',
            'ver pólizas', 'crear pólizas', 'editar pólizas', 'eliminar pólizas',
            'subir archivos de pólizas', 'renovacion de pólizas', 'pólizas vencidas', 'pólizas pendientes',
            'ver clientes', 'crear clientes',
            'gestionar sistema',
            'ver reportes', 'crear reportes', 'exportar reportes',
            'ver roles y permisos' // Asegúrate de que esté aquí
        ];
        foreach ($permisos as $permiso) {
            Permission::create(['name' => $permiso]);
        }

        // Crear roles y asignar permisos
        $admin = Role::create(['name' => 'administrador']);
        $admin->givePermissionTo($permisos); // Todos los permisos al administrador

        $user = Role::create(['name' => 'usuario']);
        $user->givePermissionTo(['ver pólizas', 'crear pólizas', 'subir archivos de pólizas', 'ver usuarios']); // Permisos limitados

        // Crear usuarios de ejemplo
        $adminUser = User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@ejemplo.com',
            'password' => Hash::make('password123'),
        ]);
        $adminUser->assignRole('administrador');

        $normalUser = User::create([
            'name' => 'Usuario Normal',
            'email' => 'usuario@ejemplo.com',
            'password' => Hash::make('password123'),
        ]);
        $normalUser->assignRole('usuario');
    }*/


    $ramos = [
        'Vida' => [
            'seguros' => ['Seguro de Vida Individual', 'Grupo vida', 'Seguro de inversión', 'De retiro'],
            'companias' => ['Thona Seguros', 'Banorte Seguros', 'Insignia Lite', 'Alianz', 'Metlife', 'General de Seguros']
        ],
        'Daños' => [
            'seguros' => ['Seguro de Daños empresa', 'casa', 'transporte'],
            'companias' => ['HDI Seguros', 'Banorte Seguros', 'Gmx Seguros', 'General de Seguros', 'Atlas Seguros']
        ],
        'Accidentes y enfermedades' => [
            'seguros' => ['Gastos Médicos Mayores', 'Accidentes Personales','Accidentes Personales Escolares'],
            'companias' => ['HDI Seguros', 'Banorte Seguros', 'Metlife', 'Alianz', 'BUPA', 'Thona Seguros','General de Seguros', 'Atlas Seguros', 'HDI Seguros']
        ],
       
        'Automóviles' => [
            'seguros' => ['Autos pickup', 'Camiones', 'Tractos'],
            'companias' => ['Banorte Seguros', 'General de Seguros', 'Atla Seguros', 'Qualitas', 'Ana Seguros','HDI Seguros']
        ]
    ];
    
    
   // Crear compañías
   $companiasInstances = [];
   foreach (array_unique(array_merge(...array_column($ramos, 'companias'))) as $nombre) {
       $companiasInstances[$nombre] = Compania::create(['nombre' => $nombre]);
   }

   // Crear ramos, seguros y asignar compañías correctamente
   foreach ($ramos as $ramoNombre => $data) {
       $ramo = Ramo::create(['nombre' => $ramoNombre]);

       foreach ($data['seguros'] as $seguroNombre) {
           $seguro = Seguro::create([
               'nombre' => $seguroNombre,
               'ramo_id' => $ramo->id
           ]);

           // Asignar compañías específicas a cada seguro
           $companiaIds = array_map(fn($nombre) => $companiasInstances[$nombre]->id, $data['companias']);
           $seguro->companias()->attach($companiaIds);
    }
}
    }
}