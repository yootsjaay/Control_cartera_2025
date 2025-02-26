@extends('adminlte::page')

@section('title', 'Crear Compañía')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="m-0 text-dark text-center">Crear Nueva Compañía</h1>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('companias.index') }}"><i class="fas fa-building"></i> Compañías</a></li>
                <li class="breadcrumb-item active"><i class="fas fa-plus"></i> Crear</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-primary" style="max-width: 700px; margin: 0 auto;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i> Nueva Compañía</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('companias.store') }}" method="POST">
                @csrf

                <!-- Campo Nombre -->
                <div class="form-group mb-3">
                    <label for="nombre">Nombre de la Compañía</label>
                    <input type="text" 
                           name="nombre" 
                           id="nombre" 
                           class="form-control @error('nombre') is-invalid @enderror" 
                           value="{{ old('nombre') }}" 
                           placeholder="Ingrese el nombre de la compañía" 
                           required>
                    @error('nombre')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Campo Clase -->
                <div class="form-group mb-3">
                    <label for="clase">Compañía a la que pertenece</label>
                    <select name="clase" 
                            id="clase" 
                            class="form-control @error('clase') is-invalid @enderror" 
                            required>
                        <option value="" disabled selected>Seleccione una compañía</option>
                        @foreach ($clases as $clase)
                            <option value="{{ $clase }}" {{ old('clase') == $clase ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $clase)) }} <!-- Mostrar nombre legible -->
                            </option>
                        @endforeach
                    </select>
                    @error('clase')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Seleccione la compañía asociada al servicio.</small>
                </div>

                <!-- Botón -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i> Crear Compañía
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <style>
        .form-group label {
            font-weight: 500;
        }
        .card {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
@endsection
