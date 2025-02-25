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
        |<div class="card-tools">
                <a href="{{ route('companias.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus-circle mr-2"></i>Nueva Compania
                </a>
            </div>

    <!-- Tabla de compañías -->
    <!-- Reemplaza la tabla -->
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
