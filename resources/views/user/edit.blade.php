@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
    <h1 class="mb-0">
        <i class="fas fa-user-edit text-primary"></i> Editar Usuario: {{ $user->name }}
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Migas de pan -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}"><i class="fas fa-users"></i> Usuarios</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <div class="card shadow">
        <div class="card-header bg-primary">
            <h3 class="card-title"><i class="fas fa-pencil-alt"></i> Formulario de Edición</h3>
        </div>
        
        <form action="{{ route('user.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Campo Nombre -->
                        <div class="form-group">
                            <label for="name" class="font-weight-bold">
                                <i class="fas fa-user"></i> Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Campo Email -->
                        <div class="form-group">
                            <label for="email" class="font-weight-bold">
                                <i class="fas fa-envelope"></i> Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Campo Grupo -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-users"></i> Grupo
                            </label>
                            <select name="group_id" class="form-control @error('group_id') is-invalid @enderror">
                                <option value="">-- Sin grupo --</option>
                                @foreach($grupos as $group)
                                    <option value="{{ $group->id }}" 
                                        {{ old('group_id', $user->group_id) == $group->id ? 'selected' : '' }}>
                                        {{ $group->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Campo Rol -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-user-tag"></i> Rol <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('role') is-invalid @enderror" 
                                    name="role" required>
                                <option value="" disabled selected>Seleccione un rol</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->name }}" 
                                         {{ old('role', $user->roles->first()->name ?? '') == $role->name ? 'selected' : '' }}>

                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

                <!-- Sección para cambiar contraseña (opcional) -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-key"></i> Cambiar Contraseña</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Nueva Contraseña</label>
                                    <input type="password" class="form-control" 
                                           name="password" placeholder="Dejar en blanco para no cambiar">
                                </div>
                                <div class="form-group">
                                    <label>Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" 
                                           name="password_confirmation">
                                </div>
                                <small class="text-muted">Mínimo 8 caracteres. Solo completar si desea cambiar la contraseña actual.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right bg-light">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="{{ route('user.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Toggle para mostrar contraseña (opcional)
        $('.toggle-password').click(function() {
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            const input = $(this).siblings('input');
            input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
        });
    });
</script>
@stop