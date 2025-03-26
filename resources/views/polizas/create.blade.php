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

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Subir Póliza</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('polizas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Selección de Compañía -->
                        <div class="mb-4">
                            <label for="compania_id" class="form-label">Compañía *</label>
                            <select class="form-select" name="compania_id" id="compania_id" required>
                                <option value="" disabled selected>Seleccione una compañía</option>
                                @foreach ($companias as $compania)
                                    <option value="{{ $compania->id }}">{{ $compania->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Selección de Ramo -->
                        <div class="mb-4">
                            <label for="ramo_id" class="form-label">Ramo *</label>
                            <div class="position-relative">
                                <select class="form-select" name="ramo_id" id="ramo_id" required disabled>
                                    <option value="" disabled selected>Primero seleccione una compañía</option>
                                </select>
                                <div id="loadingRamos" class="spinner-border spinner-border-sm text-primary d-none" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Selección de Seguro -->
                        <div class="mb-4">
                            <label for="seguro_id" class="form-label">Seguro *</label>
                            <div class="position-relative">
                                <select class="form-select" name="seguro_id" id="seguro_id" required disabled>
                                    <option value="" disabled selected>Primero seleccione un ramo</option>
                                </select>
                                <div id="loadingSeguros" class="spinner-border spinner-border-sm text-primary d-none" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Subida de PDF -->
                        <div class="mb-4">
                            <label for="pdf" class="form-label">Subir Archivo(s) PDF *</label>
                            <div class="file-drop-area" id="pdfDropZone">
                                <span class="file-msg">Arrastra archivos aquí o haz clic para seleccionar</span>
                                <input class="file-input" type="file" name="pdf[]" id="pdf" multiple required accept=".pdf">
                            </div>
                            <div class="form-text mt-2">
                                <span class="fw-bold">Requisitos:</span>
                                <ul class="mt-1">
                                    <li>Máximo 10 archivos</li>
                                    <li>Tamaño máximo por archivo: 10MB</li>
                                    <li>Solo formato PDF</li>
                                </ul>
                            </div>
                            <div id="filePreview" class="mt-3"></div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>Subir Pólizas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> 
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .file-drop-area {
        border: 2px dashed #007bff;
        border-radius: 5px;
        padding: 25px;
        text-align: center;
        transition: all 0.3s;
    }
    .file-drop-area:hover {
        background-color: #f8f9fa;
    }
    .file-input {
        display: none;
    }
    .file-msg {
        display: block;
        color: #007bff;
    }
</style>
@endsection

@section('js')  
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companiaSelect = document.getElementById('compania_id');
    const ramoSelect = document.getElementById('ramo_id');
    const seguroSelect = document.getElementById('seguro_id');
    const loadingRamos = document.getElementById('loadingRamos');
    const loadingSeguros = document.getElementById('loadingSeguros');

    // Obtener token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Función para cargar ramos por compañía
    async function cargarRamosPorCompania(companiaId) {
        try {
            loadingRamos.classList.remove('d-none');
            ramoSelect.disabled = true;
            ramoSelect.innerHTML = '<option value="" disabled selected>Cargando ramos...</option>';

            const response = await fetch('/polizas/recursos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    modelo: 'ramo',
                    id: companiaId
                })
            });

            if (!response.ok) throw new Error('Error al cargar ramos');
            
            const ramos = await response.json();
            
            ramoSelect.innerHTML = '<option value="" disabled selected>Seleccione un ramo</option>';
            ramos.forEach(ramo => {
                ramoSelect.innerHTML += `<option value="${ramo.id}">${ramo.nombre}</option>`;
            });
            
            ramoSelect.disabled = false;
            // Resetear seguros cuando cambian los ramos
            seguroSelect.innerHTML = '<option value="" disabled selected>Seleccione un ramo primero</option>';
            seguroSelect.disabled = true;

        } catch (error) {
            console.error('Error:', error);
            ramoSelect.innerHTML = '<option value="" disabled selected>Error al cargar ramos</option>';
        } finally {
            loadingRamos.classList.add('d-none');
        }
    }

    // Función para cargar seguros por ramo
    async function cargarSegurosPorRamo(ramoId) {
        try {
            loadingSeguros.classList.remove('d-none');
            seguroSelect.disabled = true;
            seguroSelect.innerHTML = '<option value="" disabled selected>Cargando seguros...</option>';

            const response = await fetch('/polizas/recursos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    modelo: 'seguro',
                    id: ramoId
                })
            });

            if (!response.ok) throw new Error('Error al cargar seguros');
            
            const seguros = await response.json();
            
            seguroSelect.innerHTML = '<option value="" disabled selected>Seleccione un seguro</option>';
            seguros.forEach(seguro => {
                const option = new Option(seguro.nombre, seguro.id);
                option.dataset.ramoId = seguro.ramo_id; // Guardar ramo_id para uso posterior
                seguroSelect.add(option);
            });
            
            seguroSelect.disabled = false;

        } catch (error) {
            console.error('Error:', error);
            seguroSelect.innerHTML = '<option value="" disabled selected>Error al cargar seguros</option>';
        } finally {
            loadingSeguros.classList.add('d-none');
        }
    }

    // Evento para compañía
    companiaSelect.addEventListener('change', function() {
        if (!this.value) {
            ramoSelect.innerHTML = '<option value="" disabled selected>Seleccione compañía primero</option>';
            ramoSelect.disabled = true;
            seguroSelect.innerHTML = '<option value="" disabled selected>Seleccione ramo primero</option>';
            seguroSelect.disabled = true;
            return;
        }
        cargarRamosPorCompania(this.value);
    });

    // Evento para ramo
    ramoSelect.addEventListener('change', function() {
        if (!this.value) {
            seguroSelect.innerHTML = '<option value="" disabled selected>Seleccione ramo primero</option>';
            seguroSelect.disabled = true;
            return;
        }
        cargarSegurosPorRamo(this.value);
    });
});
 </script>
@endsection