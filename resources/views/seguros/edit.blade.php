@extends('adminlte::page')

@section('title', 'Editar Seguro')

@section('extra-styles')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@5.15.4/css/all.min.css" rel="stylesheet" />
    <style>
        .subtipo-group {
            position: relative;
            margin-bottom: 10px;
        }
        .remove-btn {
            position: absolute;
            right: 0;
            top: 5px;
            background-color: #dc3545;
            color: white;
        }
        .add-btn-container {
            margin-top: 20px;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 10px;
            background-color: #f8f9fa;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Seguro</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('seguros.index') }}">Lista de Seguros</a></li>
        <li class="breadcrumb-item active">Editar Seguro</li>
    </ol>

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
    {{-- Formulario --}}
    <form action="{{ route('seguros.update', $seguro->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div>
            <label for="nombre">Nombre del Seguro</label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $seguro->nombre) }}" required>
        </div>
        
        <div>
            <label for="id_compania">Compañía</label>
            <select name="id_compania" id="id_compania" required>
                <option value="">Selecciona una Compañía</option>
                @foreach($companias as $compania)
                    <option value="{{ $compania->id }}" {{ old('id_compania', $seguro->id_compania) == $compania->id ? 'selected' : '' }}>
                        {{ $compania->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div id="ramos">
            <h3>Ramos</h3>
            @foreach($seguro->ramos as $index => $ramo)
                <div>
                    <label for="nombre_ramo_{{ $index }}">Nombre del Ramo {{ $index + 1 }}</label>
                    <input type="text" name="ramos[{{ $index }}][nombre_ramo]" id="nombre_ramo_{{ $index }}" value="{{ old('ramos.' . $index . '.nombre_ramo', $ramo->nombre_ramo) }}" required>
                </div>
            @endforeach
        </div>

        <button type="button" onclick="addRamo()">Agregar Otro Ramo</button>

        <button type="submit">Actualizar Seguro</button>
    </form>
</div>
@endsection

@section('js')
<script>
        let ramoCount = {{ count($seguro->ramos) }};

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
