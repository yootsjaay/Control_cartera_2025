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
    <label for="compania_id" class="form-label">Compañía</label>
    <select class="form-select" name="compania_id" id="compania_id" required>
        <option value="" disabled selected>Seleccione una compañía</option>
        @foreach ($companias as $compania)
            <option value="{{ $compania->id }}">{{ $compania->nombre_compania }}</option>
        @endforeach
    </select>
</div>

<!-- Selección de Seguro -->
<div class="mb-4">
    <label for="seguro_id" class="form-label">Seguro</label>
    <div class="position-relative">
        <select class="form-select" name="seguro_id" id="seguro_id" required disabled>
            <option value="" disabled selected>Primero seleccione una compañía</option>
        </select>
        <div id="loadingSeguros" class="spinner-border spinner-border-sm text-primary d-none" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>
</div>

<!-- Mostrar Ramo (no seleccionable, solo informativo) -->
<div class="mb-4">
    <label class="form-label">Ramo</label>
    <input type="text" class="form-control" id="ramo_nombre" readonly>
    <input type="hidden" name="ramo_id" id="ramo_id">
</div>                <!-- Subida de PDF -->
                        <div class="mb-4">
                            <label for="pdf" class="form-label">Subir Archivo(s) PDF</label>
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
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    background: #f8f9fa;
}

.file-drop-area:hover {
    border-color: #0d6efd;
    background: rgba(13, 110, 253, 0.05);
}

.file-drop-area.dragover {
    border-color: #0d6efd;
    background: rgba(13, 110, 253, 0.1);
}

.file-msg {
    color: #6c757d;
    font-size: 1rem;
    pointer-events: none;
}

#filePreview .file-item {
    background: #f1f3f5;
    border-radius: 6px;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.file-item .file-name {
    max-width: 70%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #495057;
}

.file-item .file-size {
    color: #6c757d;
    font-size: 0.875rem;
}

.spinner-border {
    width: 1.2rem;
    height: 1.2rem;
}
</style>
@endsection

@section('js')  
<script>
document.addEventListener('DOMContentLoaded', function () {
    const companiaSelect = document.getElementById('compania_id');
    const seguroSelect = document.getElementById('seguro_id');
    const ramoSelect = document.getElementById('ramo_id');
    const loadingSeguros = document.getElementById('loadingSeguros');
    const loadingRamos = document.getElementById('loadingRamos');
    
    // Función mejorada para cargar recursos
    async function cargarRecursos(modelo, id, selectElement) {
        try {
            selectElement.disabled = true;
            selectElement.innerHTML = '<option value="" disabled selected>Cargando...</option>';
            
            // Mostrar loader correspondiente
            if (modelo === 'seguro') loadingSeguros.classList.remove('d-none');
            if (modelo === 'ramo') loadingRamos.classList.remove('d-none');
            
            const response = await fetch(`/obtener-recursos?modelo=${modelo}&id=${id}`);
            
            if (!response.ok) throw new Error('Error en la respuesta');
            
            const data = await response.json();
            
            // Limpiar y preparar el select
            selectElement.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
            
            // Procesar datos según el modelo
            data.forEach(item => {
                const option = new Option(
                    item.nombre || item.nombre_ramo || item.nombre_seguro, // Compatibilidad con múltiples formatos
                    item.id
                );
                selectElement.add(option);
            });
            
            selectElement.disabled = false;
        } catch (error) {
            console.error('Error:', error);
            selectElement.innerHTML = '<option value="" disabled selected>Error al cargar</option>';
        } finally {
            // Ocultar loaders
            if (modelo === 'seguro') loadingSeguros.classList.add('d-none');
            if (modelo === 'ramo') loadingRamos.classList.add('d-none');
        }
    }

    // Evento para compañía (carga seguros)
    companiaSelect.addEventListener('change', function () {
        if (!this.value) {
            seguroSelect.disabled = true;
            ramoSelect.disabled = true;
            seguroSelect.innerHTML = '<option value="" disabled selected>Seleccione compañía primero</option>';
            ramoSelect.innerHTML = '<option value="" disabled selected>Seleccione seguro primero</option>';
            return;
        }
        
        // Cargar seguros para la compañía seleccionada
        cargarRecursos('seguro', this.value, seguroSelect)
            .then(() => {
                // Resetear ramo cuando cambia compañía
                ramoSelect.innerHTML = '<option value="" disabled selected>Seleccione seguro primero</option>';
                ramoSelect.disabled = true;
            });
    });

    // Evento para seguro (carga ramos)
    seguroSelect.addEventListener('change', function () {
        if (!this.value) {
            ramoSelect.disabled = true;
            ramoSelect.innerHTML = '<option value="" disabled selected>Seleccione seguro primero</option>';
            return;
        }
        
        // Obtener el ramo_id del seguro seleccionado (si está en los datos)
        const selectedOption = this.options[this.selectedIndex];
        const ramoId = selectedOption.dataset.ramo;
        
        if (ramoId) {
            // Si ya tenemos el ramo_id, cargar directamente
            ramoSelect.innerHTML = '<option value="" disabled selected>Cargando...</option>';
            
            // Simular carga (opcionalmente hacer nueva petición)
            setTimeout(() => {
                ramoSelect.innerHTML = `
                    <option value="" disabled selected>Seleccione...</option>
                    <option value="${ramoId}" selected>${selectedOption.text.split('(')[1]?.replace(')', '') || 'Ramo asociado'}</option>
                `;
                ramoSelect.disabled = false;
            }, 300);
        } else {
            // Si no tenemos el ramo_id, hacer petición
            cargarRecursos('ramo', this.value, ramoSelect);
        }
    });

    // [El resto de tu código para drag & drop y validación permanece igual]
});
</script>
@endsection