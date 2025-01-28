@extends('adminlte::page')

@section('title', 'Crear Nuevo Seguro')

@section('extra-styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/font-awesome@5.15.4/css/all.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Nuevo Seguro</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('seguros.index') }}">Lista de Seguros</a></li>
        <li class="breadcrumb-item active">Crear Seguro</li>
    </ol>

    {{-- Mostrar errores de validación --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Detalles del Seguro</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('seguros.store') }}" method="POST">
                @csrf

                <!-- Nombre del Seguro -->
                <div class="form-group mb-3">
                    <label for="nombre" class="form-label">Nombre del Seguro</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" required>
                </div>

                <!-- Compañía -->
                <div class="form-group mb-3">
                    <label for="compania_id" class="form-label">Compañía</label>
                    <select name="compania_id" id="compania_id" class="form-select" required>
                        <option value="">Selecciona una Compañía</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}" {{ old('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Ramos -->
                <div id="ramos">
                    <h4 class="mt-4">Ramos</h4>
                    <div class="ramo-group mb-2">
                        <input type="text" name="ramos[0][nombre_ramo]" class="form-control" placeholder="Nombre del Ramo" required>
                        <button type="button" class="remove-btn btn btn-danger mt-2" style="display: none;" onclick="removeRamo(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <!-- Botón para agregar más Ramos -->
                <button type="button" class="btn btn-secondary mt-2" onclick="addRamo()">Agregar Otro Ramo</button>

                <!-- Botón de Guardar -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Guardar Seguro</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    let ramoCount = 1;

    function addRamo() {
        const ramosDiv = document.getElementById('ramos');
        const ramoGroup = document.createElement('div');
        ramoGroup.classList.add('ramo-group', 'mb-2');
        ramoGroup.innerHTML = `
            <input type="text" name="ramos[${ramoCount}][nombre_ramo]" class="form-control" placeholder="Nombre del Ramo" required>
            <button type="button" class="remove-btn btn btn-danger mt-2" onclick="removeRamo(this)">
                <i class="fas fa-trash"></i>
            </button>
        `;
        ramosDiv.appendChild(ramoGroup);
        ramoCount++;
    }

    function removeRamo(button) {
        const ramoGroup = button.parentElement;
        ramoGroup.remove();
    }
</script>
@endsection
