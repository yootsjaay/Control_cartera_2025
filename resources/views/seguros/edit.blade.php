@extends('adminlte::page')

@section('title', 'Editar Seguro')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="m-0 text-dark text-center">Editar Seguro</h1>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('seguros.index') }}"><i class="fas fa-shield-alt"></i> Seguros</a></li>
                <li class="breadcrumb-item active">Editar Seguro</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Información del Seguro</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('seguros.update', $seguro->id) }}" method="POST" id="seguroForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre del Seguro <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="nombre" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   value="{{ old('nombre', $seguro->nombre) }}"
                                   required
                                   autofocus>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="compania_id">Compañía <span class="text-danger">*</span></label>
                            <select name="compania_id" id="compania_id" 
                                    class="form-control select2 @error('compania_id') is-invalid @enderror" 
                                    required>
                                <option value=""></option>
                                @foreach($companias as $compania)
                                    <option value="{{ $compania->id }}" 
                                        {{ old('compania_id', $seguro->compania_id) == $compania->id ? 'selected' : '' }}>
                                        {{ $compania->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('compania_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h4 class="mb-3">Ramos del Seguro <span class="text-danger">*</span></h4>
                        <div id="ramos-container">
                            @php
                                $oldRamos = old('ramos', $seguro->ramos->map(function($ramo) {
                                    return ['nombre_ramo' => $ramo->nombre_ramo];
                                })->toArray());
                            @endphp

                            @foreach($oldRamos as $index => $ramo)
                                <div class="ramo-group input-group mb-2">
                                    <input type="text" name="ramos[{{ $index }}][nombre_ramo]" 
                                           class="form-control @if($errors->has("ramos.$index.nombre_ramo")) is-invalid @endif" 
                                           value="{{ $ramo['nombre_ramo'] }}"
                                           placeholder="Nombre del ramo"
                                           required>
                                    <div class="input-group-append">
                                        @if($loop->first)
                                            <button type="button" class="btn btn-success add-ramo">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-danger remove-ramo">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                    @if($errors->has("ramos.$index.nombre_ramo"))
                                        <div class="invalid-feedback">
                                            {{ $errors->first("ramos.$index.nombre_ramo") }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <small class="form-text text-muted">Mínimo 1 ramo requerido</small>
                        @error('ramos')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12 text-right">
                        <a href="{{ route('seguros.index') }}" class="btn btn-default">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Actualizar Seguro
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Inicializar Select2 con configuración mejorada
    $('#compania_id').select2({
        placeholder: "Seleccione una compañía",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#seguroForm')
    });

    // Manejo dinámico de ramos con validación
    let ramoIndex = {{ old('ramos') ? count(old('ramos')) : 1 }};
    const ramosContainer = $('#ramos-container');
    
    // Agregar nuevo ramo
    ramosContainer.on('click', '.add-ramo', function() {
        const newGroup = $('<div class="ramo-group input-group mb-2">')
            .append(
                $('<input>').attr({
                    type: 'text',
                    name: `ramos[${ramoIndex}][nombre_ramo]`,
                    class: 'form-control',
                    placeholder: 'Nombre del ramo',
                    required: true
                }),
                $('<div class="input-group-append">').append(
                    $('<button>').attr({
                        type: 'button',
                        class: 'btn btn-danger remove-ramo'
                    }).html('<i class="fas fa-times"></i>')
                )
            );
        
        ramosContainer.append(newGroup);
        ramoIndex++;
        actualizarBotonesRemove();
    });

    // Eliminar ramo
    ramosContainer.on('click', '.remove-ramo', function() {
        if($('.ramo-group').length > 1) {
            $(this).closest('.ramo-group').remove();
            reindexRamos();
            actualizarBotonesRemove();
        }
    });

    // Reindexar ramos
    function reindexRamos() {
        ramoIndex = 0;
        $('.ramo-group').each(function(index) {
            $(this).find('input').attr('name', `ramos[${index}][nombre_ramo]`);
            ramoIndex = index + 1;
        });
    }

    // Actualizar visibilidad de botones de eliminar
    function actualizarBotonesRemove() {
        $('.ramo-group').each(function(index) {
            const removeBtn = $(this).find('.remove-ramo');
            removeBtn.toggle(index > 0);
        });
    }

    // Validación del formulario
    $('#seguroForm').on('submit', function(e) {
        if($('.ramo-group').length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe agregar al menos un ramo',
                confirmButtonColor: '#3085d6'
            });
        }
    });

    // Mostrar errores de validación para ramos
    @if($errors->any())
        @foreach($errors->keys() as $key)
            @if(strpos($key, 'ramos.') === 0)
                const errorKey = "{{ str_replace('.', '][', $key) }}";
                const errorField = $(`[name="${errorKey}"]`);
                errorField.addClass('is-invalid');
                if(!errorField.next('.invalid-feedback').length) {
                    errorField.after(
                        `<div class="invalid-feedback">{{ $errors->first($key) }}</div>`
                    );
                }
            @endif
        @endforeach
    @endif
});
</script>
@endsection