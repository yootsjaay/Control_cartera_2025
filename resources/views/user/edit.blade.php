@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
    <h1>Editar Usuario: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña (dejar en blanco para no cambiar):</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Contraseña:</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
                <div class="form-group">
                    <label>Grupos:</label>
                    <select name="groups[]" class="form-control" multiple required>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ $user->groups->contains($group->id) ? 'selected' : '' }}>{{ $group->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Roles:</label>
                    @foreach($roles as $role)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}" {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_{{ $role->id }}">
                                {{ $role->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="form-group">
                    <label>Opciones de Token API:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="generate_token" id="generate_token" value="1">
                        <label class="form-check-label" for="generate_token">
                            Generar nuevo token de acceso API
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="revoke_tokens" id="revoke_tokens" value="1">
                        <label class="form-check-label" for="revoke_tokens">
                            Revocar todos los tokens existentes
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
