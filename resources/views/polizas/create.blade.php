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

                        <!-- Selección de Compañía -->
                        <div class="mb-3">
                            <label for="compania_id" class="form-label">Compañía</label>
                            <select class="form-select" name="compania_id" id="compania_id" required>
                                <option value="" disabled selected>Seleccione una compañía</option>
                                @foreach ($companias as $compania)
                                    <option value="{{ $compania->id }}">{{ $compania->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Selección de Tipo de Seguro -->
                        <div class="mb-3">
                            <label for="seguro_id" class="form-label">Seguro</label>
                            <select class="form-select" name="seguro_id" id="seguro_id" required>
                                <option value="" disabled selected>Seleccione un seguro</option>
                                <!-- Opciones cargadas dinámicamente -->
                            </select>
                        </div>

                        <!-- Selección de Ramo -->
                        <div class="mb-3">
                            <label for="ramo_id" class="form-label">Ramo</label>
                            <select class="form-select" name="ramo_id" id="ramo_id" required>
                                <option value="" disabled selected>Seleccione un ramo</option>
                                <!-- Opciones cargadas dinámicamente -->
                            </select>
                            <div id="loadingRamos" class="form-text text-muted d-none">Cargando ramos...</div>
                        </div>

                    
                        <!-- Subida de PDF -->
                    <div class="mb-3">
                        <label for="pdf" class="form-label">Subir Archivo(s) PDF</label>
                        <input class="form-control" type="file" name="pdf[]" id="pdf" multiple required accept=".pdf">
                        <div class="form-text">Puedes seleccionar varios archivos presionando <b>Ctrl</b> o <b>Shift</b> mientras seleccionas.</div>
                        <small class="text-muted">Límite máximo de 10 archivos. Cada archivo no debe superar los 10MB.</small>
                    </div>


                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Subir Pólizas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> 
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
@stop

@section('js')  
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companiaSelect = document.getElementById('compania_id');
    const seguroSelect = document.getElementById('seguro_id');
    const ramoSelect = document.getElementById('ramo_id');
    const loadingRamos = document.getElementById('loadingRamos');

    // Al seleccionar una compañía, cargar los seguros relacionados
    companiaSelect.addEventListener('change', function () {
        const companiaId = this.value;

        if (companiaId) {
            seguroSelect.innerHTML = '<option value="" disabled selected>Seleccione un seguro</option>';
            ramoSelect.innerHTML = '<option value="" disabled selected>Seleccione un ramo</option>';
            
            fetch(`/obtener-seguros/${companiaId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(seguro => {
                        const option = document.createElement('option');
                        option.value = seguro.id;
                        option.textContent = seguro.nombre;
                        seguroSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar seguros:', error);
                });
        }
    });

    // Al seleccionar un seguro, cargar los ramos relacionados
    seguroSelect.addEventListener('change', function () {
        const seguroId = this.value;

        if (seguroId) {
            ramoSelect.innerHTML = '<option value="" disabled selected>Seleccione un ramo</option>';
            loadingRamos.classList.remove('d-none');

            fetch(`/obtener-ramos/${seguroId}`)
                .then(response => response.json())
                .then(data => {
                    loadingRamos.classList.add('d-none');
                    data.forEach(ramos => {
                        const option = document.createElement('option');
                        option.value = ramos.id;
                        option.textContent = ramos.nombre_ramo;
                        ramoSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar ramos:', error);
                    loadingRamos.textContent = 'Error al cargar ramos. Intenta nuevamente.';
                    setTimeout(() => loadingRamos.classList.add('d-none'), 3000);
                });
        }
    });
});
</script>
@stop