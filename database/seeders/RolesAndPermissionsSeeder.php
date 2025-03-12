<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Resetear caché de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Crear permisos con guard_name explícito
        $this->createPermissions();
        
        // 3. Crear roles y asignar permisos
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // 4. Asignar todos los permisos a admin
        $adminRole->givePermissionTo(Permission::all());

        // 5. Permisos específicos para usuario regular
        $userRole->givePermissionTo([
            'ver pólizas',
            'ver clientes',
            'subir archivos de pólizas' // Agregado para consistencia
        ]);

        // 6. Crear usuario admin con mejores prácticas
        $this->createAdminUser();
    }

    private function createPermissions(): void
    {
        $permissions = [
            // Pólizas
            'crear pólizas', 
            'ver pólizas',
            'editar pólizas',
            'eliminar pólizas',
            'subir archivos de pólizas',
            'renovacion de pólizas',
            'pólizas vencidas', // Corregido a plural
            'pólizas pendientes', // Corregido a plural

            // Clientes
            'crear clientes',
            'ver clientes',
            'editar clientes',
            'eliminar clientes',

            // Usuarios y Roles
            'crear usuarios',
            'ver usuarios',
            'editar usuarios',
            'eliminar usuarios',
            'asignar roles',
            'ver roles y permisos',

            // Reportes
            'crear reportes',
            'ver reportes',
            'editar reportes',
            'eliminar reportes',
            'imprimir reportes',
            'descargar reportes',
            'exportar reportes'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
    }

    private function createAdminUser(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@seguros.com'],
            [
                'name' => 'Administrador del Sistema',
                'password' => bcrypt('AdminSecurePassword123!'), // Contraseña más segura
                'email_verified_at' => now() // Verificar email automáticamente
            ]
        )->assignRole('admin');
    }
}