<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Notifications\DatabaseNotification;


class UserController extends Controller
{


    public function index()
    {
        $user = User::with('roles', 'group')->paginate(10); // Ejemplo de paginación
        return view('user.index', compact('user'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $grupos = Group::all();
        $roles = Role::all();
        return view('user.create', compact('grupos', 'roles'));
    }

    
   // En UserController.php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required',
        'role' => 'required|exists:roles,name',
        'group_id' => 'nullable|exists:groups,id' // Validación para grupo (opcional)
    ]);

    // Crear usuario
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'group_id' => $validated['group_id'] ?? null // Asignar grupo si existe
    ]);

    // Asignar rol (usando Spatie)
    $user->assignRole($validated['role']);

    // Generar token de acceso (para API)
    $token = $user->createToken('auth_token')->plainTextToken;

    return redirect()->route('user.index')->with([
        'success' => 'Usuario registrado correctamente',
        'token' => $token,
        'new_user_id' => $user->id
    ]);
}
    

 
    /**
     * Muestra la lista de usuarios.
     *
     * @return \Illuminate\View\View
     */


public function edit(string $id)
{
    $user = User::findOrFail($id); // Corregir la variable a usuario
    $roles = Role::all();
    $grupos = Group::all();

    return view('user.edit', compact('user', 'roles','grupos')); // Corregir el nombre de la variable a 'usuario'
}

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, User $user)
{
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'role' => 'required|exists:roles,name',
        'group_id' => 'nullable|exists:groups,id',
        'password' => 'nullable|min:8|confirmed' // Opcional
    ];

    $validated = $request->validate($rules);

    // Actualizar datos básicos
    $user->update($request->only('name', 'email', 'group_id'));

    // Actualizar contraseña (si se proporcionó)
    if ($request->filled('password')) {
        $user->update(['password' => bcrypt($validated['password'])]);
    }

    // Sincronizar rol
    $user->syncRoles([$validated['role']]);

    return redirect()->route('user.index')
        ->with('success', 'Usuario actualizado correctamente');
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



public function notificaciones()
{
    $notificaciones = auth()->user()->notifications()->paginate(10);
    return view('notificaciones.index', compact('notificaciones'));
}

public function marcarComoLeida(DatabaseNotification $notificacion)
{
    $this->authorize('update', $notificacion);
    $notificacion->markAsRead();
    return back()->with('success', 'Notificación marcada como leída');
}

public function marcarTodasComoLeidas()
{
    auth()->user()->unreadNotifications->markAsRead();
    return back()->with('success', 'Todas las notificaciones marcadas como leídas');
}

}
