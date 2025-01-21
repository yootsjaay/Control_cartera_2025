<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $usuarios = User::all();
    return view('user.index', compact('usuarios'));
}

/**
 * Show the form for creating a new resource.
 */
public function create()
{
    $roles = Role::all();
    return view('user.create', compact('roles'));
}

/**
 * Store a newly created resource in storage.
 */
public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
        'roles' => 'required'
    ]);

    $usuario = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);
        // Paso 1: Obtener los nombres de los roles basados en los IDs enviados en el formulario

    $roleNames = Role::whereIn('id', $request->roles)->pluck('name')->toArray();

    $usuario->syncRoles($roleNames);

    return redirect()->route('user.index')->with('success', 'Usuario creado correctamente.');
}

/**
 * Display the specified resource.
 */
public function show(string $id)
{
    // Implementar si es necesario
}

/**
 * Show the form for editing the specified resource. 
 */
public function edit(string $id)
{
    $usuario = User::findOrFail($id); // Corregir la variable a usuario
    $roles = Role::all();
    return view('user.edit', compact('usuario', 'roles')); // Corregir el nombre de la variable a 'usuario'
}

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, $id)
{
    $usuario = User::findOrFail($id);

    // Validación de los datos
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        'password' => 'nullable|string|min:8|confirmed',
        'roles' => 'array|nullable'
    ]);

    // Actualización de los datos del usuario
    $usuario->name = $request->input('name');
    $usuario->email = $request->input('email');

    // Solo actualiza la contraseña si se ha llenado el campo
    if ($request->filled('password')) {
        $usuario->password = bcrypt($request->input('password'));
    }

    // Sincroniza los roles si se envían
    if ($request->has('roles')) {
        $usuario->roles()->sync($request->input('roles'));
    }

    $usuario->save();

    return redirect()->route('user.index')->with('success', 'Usuario actualizado exitosamente');
}


/**
 * Remove the specified resource from storage.
 */
public function destroy(string $id)
{
    // Encontrar el usuario por su ID
    $usuario = User::findOrFail($id);
    
    // Eliminar al usuario
    $usuario->delete();
    
    // Redirigir con un mensaje de éxito
    return redirect()->route('user.index')->with('success', 'Usuario eliminado correctamente.');
}

    
}
