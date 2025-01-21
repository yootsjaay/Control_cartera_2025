@extends('adminlte::page')

@section('title', 'Subir Póliza')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Gestión de Pólizas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('companias.index') }}">Compañía</a></li>
        <li class="breadcrumb-item active">Registrar</li>
    </ol>

    <!-- Mensajes de éxito y error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Éxito!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>¡Error!</strong> Por favor corrige los siguientes problemas:
            <ul class="mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Formulario de carga -->
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Subir Póliza</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('polizas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="compania_id1" class="form-label">Compañía</label>
                            <select class="form-select" name="compania_id" id="compania_id1" required>
                                <option value="" disabled selected>Seleccione una compañía</option>
                                @foreach ($companias as $compania)
                                    <option value="{{ $compania->id }}">{{ $compania->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_seguro_id1" class="form-label">Tipo de Seguro</label>
                            <select class="form-select" name="tipo_seguro_id" id="tipo_seguro_id1" required>
                                <option value="" disabled selected>Seleccione un tipo de seguro</option>
                                @foreach ($seguros as $seguro)
                                    <option value="{{ $seguro->id }}">{{ $seguro->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subtipo_seguro_id1" class="form-label">Subtipo de Seguro</label>
                            <select class="form-select" name="subtipo_seguro_id" id="subtipo_seguro_id1" required>
                                <option value="" disabled selected>Seleccione un subtipo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="pdf" class="form-label">Subir Archivo(s) PDF</label>
                            <input class="form-control" type="file" name="pdf[]" multiple required>
                            <div class="form-text">Puedes seleccionar varios archivos presionando <b>Ctrl</b> o <b>Shift</b> mientras seleccionas.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Subir Pólizas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
@stop

@section('js')
<script>
   document.addEventListener('DOMContentLoaded', function () {
    // Evento para actualizar los subtipos cuando se selecciona un tipo de seguro
    document.getElementById('tipo_seguro_id1').addEventListener('change', function () {
        const tipoSeguroId = this.value;

        // Solo hacer la solicitud si se ha seleccionado un tipo de seguro
        if (tipoSeguroId) {
            fetch(`/obtener-subtipos/${tipoSeguroId}`)
                .then(response => response.json())
                .then(data => {
                    // Limpiar el select de subtipos
                    const subtipoSelect = document.getElementById('subtipo_seguro_id1');
                    subtipoSelect.innerHTML = '<option value="" disabled selected>Seleccione un subtipo</option>';

                    // Agregar los subtipos al select
                    data.forEach(subtipo => {
                        const option = document.createElement('option');
                        option.value = subtipo.id;
                        option.textContent = subtipo.nombre;
                        subtipoSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Hubo un error al cargar los subtipos. Intente nuevamente.');
                });
        } else {
            // Limpiar el select de subtipos si no se seleccionó tipo de seguro
            document.getElementById('subtipo_seguro_id1').innerHTML = '<option value="" disabled selected>Seleccione un subtipo</option>';
        }
    });
});

</script>
@stop
