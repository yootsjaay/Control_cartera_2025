@extends('adminlte::page')

@section('title', 'Lista Compañías')

@section('extra-styles')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Compañías</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Compañías</li>
    </ol>
    
    <!-- Botón para abrir el modal -->
    <div class="mb-4">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearCompaniaModal">
            Registrar Compañía
        </button>
    </div>

    <!-- Tabla de compañías -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Lista de Compañías
        </div>
        <div class="card-body">
            <table id="listaCompania" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Fecha de Creación</th>
                        <th>Fecha de Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companias as $compania)
                        <tr>
                            <td>{{ $compania->nombre }}</td>
                            <td>{{ $compania->slug }}</td>
                            <td>{{ $compania->created_at->format('d/m/Y') }}</td>
                            <td>{{ $compania->updated_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('companias.edit', $compania->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                <form action="{{ route('companias.destroy', $compania->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta compañía?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No hay compañías registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para crear una compañía -->
<div class="modal fade" id="crearCompaniaModal" tabindex="-1" aria-labelledby="crearCompaniaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearCompaniaModalLabel">Registrar Compañía</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('companias.store') }}" method="POST" id="crearCompaniaForm">
                    @csrf

                    <!-- Nombre -->
                    <div class="form-group mb-3">
                        <label for="nombre">Nombre de la Compañía</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                        @error('nombre')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div class="form-group mb-3">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" required>
                        @error('slug')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Clase -->
                    <div class="form-group mb-3">
                        <label for="clase">Clase</label>
                        <select name="clase" id="clase" class="form-control" required>
                            <option value="" disabled selected>Seleccione una clase</option>
                            @foreach ($clases as $clase => $nombre)
                                <option value="{{ $clase }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                        @error('clase')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
<script>
    $(document).ready(function() {
        $('#listaCompania').DataTable({
            responsive: true,
            order: [[2, 'desc']],
            columnDefs: [
                { orderable: false, targets: [4] }, // Deshabilitar orden en columna de acciones
                { className: "dt-body-center", targets: [0, 1, 2, 3] }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-MX.json',
            },
        });
    });
</script>
</script>
@endsection
