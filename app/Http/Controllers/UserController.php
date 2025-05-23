<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['groups', 'roles'])->latest()->paginate(10);
        return view('user.index', compact('users'));
    }

    public function create()
    {
        $groups = Group::all();
        $roles = Role::all();
        return view('user.create', compact('groups', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'groups' => ['required', 'array'],
            'groups.*' => ['exists:groups,id'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            'generate_token' => ['nullable', 'boolean']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->groups()->attach($request->groups); // Asignar grupos

        $roles = Role::whereIn('id', $request->roles)->pluck('name');
        $user->syncRoles($roles);

        $tokenMessage = '';
        if ($request->generate_token) {
            $token = $user->createToken('api-token')->plainTextToken;
            $tokenMessage = ' | Token API: ' . $token;
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente' . $tokenMessage)
            ->with('token', $request->generate_token ? $token : null);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $groups = Group::all();
        $roles = Role::all();
        return view('user.edit', compact('user', 'groups', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class . ',id,' . $user->id],
            'groups' => ['required', 'array'],
            'groups.*' => ['exists:groups,id'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $user->groups()->sync($request->groups); // Sincronizar grupos

        $roles = Role::whereIn('id', $request->roles)->pluck('name');
        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente');
    }
}