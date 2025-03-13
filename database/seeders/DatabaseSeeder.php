<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Crear permisos
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
    }
}