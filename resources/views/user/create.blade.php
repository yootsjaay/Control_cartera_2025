@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
    <h1>Crear Nuevo Usuario</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Contraseña:</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Grupos:</label>
                    <select name="groups[]" class="form-control" multiple required>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Roles:</label>
                    @foreach($roles as $role)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}">
                            <label class="form-check-label" for="role_{{ $role->id }}">
                                {{ $role->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="form-group">
                    <label for="generate_token">Generar Token API:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="generate_token" id="generate_token" value="1">
                        <label class="form-check-label" for="generate_token">
                            Generar token de acceso API para este usuario
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
