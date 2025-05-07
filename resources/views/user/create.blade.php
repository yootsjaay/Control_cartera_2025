@extends('adminlte::page')

@section('title', 'Registrar Nuevo Usuario')

@section('content_header')
    <h1 class="mb-0">
        <i class="fas fa-user-plus text-primary"></i> Registrar Usuario
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Migas de pan -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.index') }}"><i class="fas fa-users"></i> Usuarios</a></li>
            <li class="breadcrumb-item active">Nuevo</li>
        </ol>
    </nav>

    <div class="card shadow">
        <div class="card-header bg-primary">
            <h3 class="card-title"><i class="fas fa-user-edit"></i> Formulario de Registro</h3>
        </div>
        
        <form action="{{ route('user.store') }}" method="POST" id="user-form">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Campo Nombre -->
                        <div class="form-group">
                            <label for="name" class="font-weight-bold">
                                <i class="fas fa-user"></i> Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" 
                                   placeholder="Ej: María González" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                        <label><i class="fas fa-users"></i> Grupo</label>
                        <select class="form-control" name="group_id">
                            @foreach($grupos as $group)
                                <option value="{{ $group->id }}">{{ $group->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                      <!-- Campo Email -->
                        <div class="form-group">
                            <label for="email" class="font-weight-bold">
                                <i class="fas fa-envelope"></i> Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}"
                                   placeholder="Ej: usuario@dominio.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Campo Contraseña -->
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-lock"></i> Contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" id="password" 
                                       placeholder="Mínimo 8 caracteres" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Incluye mayúsculas, números y símbolos</small>
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
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
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

            <div class="card-footer text-right bg-light">
                <button type="submit" class="btn btn-primary" id="submit-btn">
                    <i class="fas fa-save"></i> Guardar Usuario
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
        // Mostrar/ocultar contraseña
        $('.toggle-password').click(function() {
            const icon = $(this).find('i');
            const input = $('#password');
            input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
            icon.toggleClass('fa-eye fa-eye-slash');
        });

        // Validación antes de enviar
        $('#user-form').submit(function() {
            $('#submit-btn').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        });
    });
</script>
@stop