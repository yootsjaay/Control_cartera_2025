<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Mostrar una lista de los roles.
     */


    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    /**
     * Mostrar el formulario para crear un nuevo rol.
     */
    public function create()
    {
        $permissions = Permission::all(); // Obtener todos los permisos disponibles
        return view('roles.create', compact('permissions'));
    }

    /**
     * Almacenar un rol recién creado en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar la entrada
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array', // Acepta un array de permisos
        ]);
    
        // Crear el nuevo rol
        $role = Role::create(['name' => $request->name]);
    
        // Asignar los permisos si existen
        if ($request->permissions) {
            // Obtiene los nombres de los permisos en lugar de los IDs
            $role->syncPermissions($request->permissions);
        }
    
        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Mostrar un rol específico.
     */
    public function show($id)
    {
        $role = Role::findOrFail($id);
        return view('roles.show', compact('role'));
    }

    /**
     * Mostrar el formulario para editar un rol.
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all(); // Obtener todos los permisos
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Actualizar un rol en la base de datos.
     */
    public function update(Request $request, Role $role)
{
    // Validar la entrada
    $request->validate([
        'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        'permissions' => 'array', // Acepta un array de permisos
    ]);

    // Actualizar el nombre del rol
    $role->update(['name' => $request->name]);

    // Asignar los permisos si existen
    if ($request->permissions) {
        // Obtener los nombres de los permisos basados en los IDs enviados
        $permissionNames = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();

        // Sincronizar permisos con el rol
        $role->syncPermissions($permissionNames);
    }

    return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
}


    /**
     * Eliminar un rol de la base de datos.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
    }
}
