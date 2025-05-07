<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Group;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 🔑 Lista de permisos organizados por entidad
        $acciones = ['ver', 'crear', 'editar', 'eliminar'];
        $modulos = ['usuarios', 'polizas', 'roles', 'grupos'];

        $permisos = [];

        foreach ($modulos as $modulo) {
            foreach ($acciones as $accion) {
                $permisos[] = "$accion $modulo";
                Permission::firstOrCreate(['name' => "$accion $modulo"]);
            }
        }

        // 🎩 Crear roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $usuario = Role::firstOrCreate(['name' => 'usuario']);

        // 🔐 Asignar permisos
        $admin->syncPermissions($permisos);
        $usuario->syncPermissions([
            'ver polizas', 'crear polizas', 'editar polizas', 'ver usuarios'
        ]);

        // 👥 Crear grupos
        $internos = Group::firstOrCreate(['nombre' => 'Agentes Internos']);
        $externos = Group::firstOrCreate(['nombre' => 'Agentes Externos']);

        // 👤 Crear usuario admin
        $adminUser = User::firstOrCreate([
            'email' => 'cardozob76121@gmail.com'
        ], [
            'name' => 'admin',
            'password' => Hash::make('cardozo@24'),
            'group_id' => $internos->id,
        ]);
        $adminUser->assignRole('admin');

        // 🔐 Crear token para el admin (opcional)
        $token = $adminUser->createToken('token-admin')->plainTextToken;
        echo "TOKEN PARA PYTHON (ADMIN): $token\n";
    }
}
