<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;


class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos para pólizas
        Permission::firstOrCreate(['name' => 'crear pólizas']);
        Permission::firstOrCreate(['name' => 'ver pólizas']);
        Permission::firstOrCreate(['name' => 'editar pólizas']);
        Permission::firstOrCreate(['name' => 'eliminar pólizas']);
        Permission::firstOrCreate(['name' => 'subir archivos de pólizas']);
        Permission::firstOrCreate(['name' => 'renovacion de pólizas']);
        Permission::firstOrCreate(['name' => 'pólizas vencida']);
        Permission::firstOrCreate(['name' => 'pólizas pendiente']);

        // Crear permisos para clientes o asegurados
        Permission::firstOrCreate(['name' => 'crear clientes']);
        Permission::firstOrCreate(['name' => 'ver clientes']);
        Permission::firstOrCreate(['name' => 'editar clientes']);
        Permission::firstOrCreate(['name' => 'eliminar clientes']);

        // Crear permisos para usuarios y roles
        Permission::firstOrCreate(['name' => 'crear usuarios']);
        Permission::firstOrCreate(['name' => 'ver usuarios']);
        Permission::firstOrCreate(['name' => 'editar usuarios']);
        Permission::firstOrCreate(['name' => 'eliminar usuarios']);
        Permission::firstOrCreate(['name' => 'asignar roles']);
        Permission::firstOrCreate(['name' => 'ver roles y permisos']);

        // Crear permisos para reportes
        Permission::firstOrCreate(['name' => 'crear reportes']);
        Permission::firstOrCreate(['name' => 'ver reportes']);
        Permission::firstOrCreate(['name' => 'editar reportes']);
        Permission::firstOrCreate(['name' => 'eliminar reportes']);
        Permission::firstOrCreate(['name' => 'imprimir reportes']);
        Permission::firstOrCreate(['name' => 'descargar reportes']);
        Permission::firstOrCreate(['name' => 'exportar reportes']);

        // Crear roles y asignar permisos
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Asignar todos los permisos al rol de administrador
        $adminRole->givePermissionTo(Permission::all());

        // Asignar permisos específicos al rol de usuario
        $userRole->givePermissionTo([
            'ver pólizas',
            'ver clientes',
        ]);

        // Crear un usuario administrador por defecto
        $adminUser = User::firstOrCreate([
            'email' => 'admin@gmail.com' // Cambia el correo por el que prefieras
        ], [
            'name' => 'Administrador',
            'password' => bcrypt('admin123') // Cambia la contraseña por una segura
        ]);

        // Asignar el rol de 'admin' al usuario administrador
        $adminUser->assignRole('admin');
    
    }
}
