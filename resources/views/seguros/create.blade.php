@extends('adminlte::page')

@section('title', 'Crear Nuevo Seguro')

@section('extra-styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/font-awesome@5.15.4/css/all.min.css" rel="stylesheet" />
<style>
    .subtipo-group {
        margin-bottom: 10px;
    }
    .remove-btn {
        background-color: #dc3545;
        color: white;
    }
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        border-radius: 10px;
        background-color: #f8f9fa;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Nuevo Seguro</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('seguros.index') }}">Lista</a></li>
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

    <div class="form-container">
    <form action="{{ route('seguros.store') }}" method="POST">
        @csrf
        <div>
            <label for="nombre">Nombre del Seguro</label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required>
        </div>
        
        <div>
            <label for="compania_id">Compañía</label>
            <select name="compania_id" id="compania_id" required>
                <option value="">Selecciona una Compañía</option>
                @foreach($companias as $compania)
                    <option value="{{ $compania->id }}" {{ old('compania_id') == $compania->id ? 'selected' : '' }}>
                        {{ $compania->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div id="ramos">
            <h3>Ramos</h3>
            <div>
                <label for="nombre_ramo_0">Nombre del Ramo</label>
                <input type="text" name="ramos[0][nombre_ramo]" id="nombre_ramo_0" required>
            </div>
        </div>

        <button type="button" onclick="addRamo()">Agregar Otro Ramo</button>

        <button type="submit">Guardar Seguro</button>
    </form>
    </div>
</div>
@endsection

@section('js')
<script>
        let ramoCount = 1;

        function addRamo() {
            const ramoDiv = document.createElement('div');
            ramoDiv.innerHTML = `
                <label for="nombre_ramo_${ramoCount}">Nombre del Ramo ${ramoCount + 1}</label>
                <input type="text" name="ramos[${ramoCount}][nombre_ramo]" id="nombre_ramo_${ramoCount}" required>
            `;
            document.getElementById('ramos').appendChild(ramoDiv);
            ramoCount++;
        }
    </script>
@stop
