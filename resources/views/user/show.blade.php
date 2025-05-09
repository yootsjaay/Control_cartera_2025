@extends('adminlte::page')

@section('title', 'Detalles del Usuario')

@section('content_header')
    <h1>Detalles del Usuario: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID:</strong> {{ $user->id }}</p>
                    <p><strong>Nombre:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Grupo:</strong> {{ $user->group->nombre ?? 'Sin grupo' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Roles:</strong></p>
                    <ul>
                        @foreach($user->roles as $role)
                            <li>{{ $role->name }}</li>
                        @endforeach
                    </ul>
                    <p><strong>Creado:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Actualizado:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">Editar</a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
@stop