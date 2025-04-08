@extends('adminlte::page')

@section('title', 'Subir Póliza')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Gestión de Pólizas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('polizas.index') }}">Lista de Polizas</a></li>
        <li class="breadcrumb-item active">Registrar</li>
    </ol>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-2"></i>
                <div>
                    <strong>¡Éxito!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-2"></i>
                <div>
                    <strong>¡Error!</strong> Por favor corrige los siguientes problemas:
                    <ul class="mt-2 mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="card-title mb-0"><i class="fas fa-file-pdf me-2"></i>Subir Póliza</h3>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('polizas.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <div class="row g-4">
                            <!-- Selección de Compañía -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="compania_id" id="compania_id" required>
                                        <option value="" disabled selected>Seleccione una compañía</option>
                                        @foreach ($companias as $compania)
                                            <option value="{{ $compania->id }}">{{ $compania->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <label for="compania_id">Compañía *</label>
                                </div>
                            </div>

                            <!-- Selección de Ramo -->
                            <div class="col-md-6">
                                <div class="form-floating position-relative">
                                    <select class="form-select" name="ramo_id" id="ramo_id" required disabled>
                                        <option value="" disabled selected>Cargando...</option>
                                    </select>
                                    <label for="ramo_id">Ramo *</label>
                                    <div id="loadingRamos" class="position-absolute end-0 top-50 translate-middle-y me-3">
                                        <div class="spinner-border spinner-border-sm text-primary d-none" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Selección de Seguro -->
                            <div class="col-md-6">
                                <div class="form-floating position-relative">
                                    <select class="form-select" name="seguro_id" id="seguro_id" required disabled>
                                        <option value="" disabled selected>Cargando...</option>
                                    </select>
                                    <label for="seguro_id">Seguro *</label>
                                    <div id="loadingSeguros" class="position-absolute end-0 top-50 translate-middle-y me-3">
                                        <div class="spinner-border spinner-border-sm text-primary d-none" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subida de PDF -->
                            <div class="col-12">
                                <div class="file-drop-area rounded-3 border-2 border-dashed border-primary bg-light" id="pdfDropZone">
                                    <div class="file-msg text-center p-4">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                        <p class="mb-1 fw-bold">Arrastra tus archivos PDF aquí</p>
                                        <p class="mb-0 text-muted">o haz clic para seleccionar</p>
                                        <p class="mt-2 mb-0 small text-muted">Máximo 10 archivos • Máximo 10MB por archivo</p>
                                    </div>
                                    <input class="file-input" type="file" name="pdf[]" id="pdf" multiple required accept=".pdf">
                                </div>
                                <div id="filePreview" class="row g-2 mt-3"></div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg py-3">
                                <i class="fas fa-upload me-2"></i>Procesar Pólizas
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
        transition: all 0.3s ease;
        position: relative;
    }
    
    .file-drop-area:hover {
        background-color: rgba(0, 123, 255, 0.05);
        border-color: #0056b3;
    }
    
    .file-drop-area.dragover {
        background-color: rgba(0, 123, 255, 0.1);
        border-color: #004085;
        box-shadow: 0 0 15px rgba(0, 123, 255, 0.2);
    }
    
    .file-preview-item {
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
    }
    
    .file-preview-item .remove-file {
        cursor: pointer;
        margin-left: auto;
        color: #dc3545;
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