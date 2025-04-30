<!-- resources/views/polizas/index.blade.php -->
@extends('adminlte::page')

@section('title', 'Pólizas')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Lista de Pólizas</h3>
        <div class="card-tools">
            <a href="{{ route('polizas.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus-circle mr-2"></i> Nueva Póliza
            </a>
        </div>
    </div>

    <div class="card-body">
        <form id="filtrosForm" method="GET" class="row mb-3">
            <div class="col-md-2">
                <label for="fecha_inicio">Fecha inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
            </div>
            <div class="col-md-2">
                <label for="fecha_fin">Fecha fin</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
            </div>
            <div class="col-md-2">
                <label for="companiaFilter">Compañía</label>
                <select id="companiaFilter" name="companiaFilter" class="form-control">
                    <option value="">Todas</option>
                    @foreach($companias as $compania)
                        <option value="{{ $compania->id }}" {{ request('companiaFilter') == $compania->id ? 'selected' : '' }}>{{ $compania->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="tipoFilter">Tipo Prima</label>
                <select id="tipoFilter" name="tipoFilter" class="form-control">
                    <option value="">Todos</option>
                    <option value="Anual" {{ request('tipoFilter') == 'Anual' ? 'selected' : '' }}>Anual</option>
                    <option value="Fraccionado" {{ request('tipoFilter') == 'Fraccionado' ? 'selected' : '' }}>Fraccionado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="statusFilter">Estado</label>
                <select id="statusFilter" name="statusFilter" class="form-control">
                    <option value="">Todos</option>
                    <option value="vigente" {{ request('statusFilter') == 'vigente' ? 'selected' : '' }}>Vigente</option>
                    <option value="vencida" {{ request('statusFilter') == 'vencida' ? 'selected' : '' }}>Vencida</option>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('polizas.index') }}" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>

        <div class="table-responsive">
            <table id="polizasTable" class="table table-striped table-hover w-100">
                <thead class="bg-lightblue">
                    <tr>
                        <th>Póliza</th>
                        <th>Cliente</th>
                        <th>Compañía</th>
                        <th>Vigencia</th>
                        <th>Monto</th>
                        <th>Seguro</th>
                        <th>Ramo</th>
                        <th>Documento</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($polizas as $poliza)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong>#{{ $poliza->numeros_poliza->numero_poliza ?? 'N/A' }}</strong>
                                    <small class="text-muted">Creada: {{ $poliza->created_at->format('d/m/Y') }}</small>
                                </div>
                            </td>
                            <td>{{ $poliza->nombre_cliente }}</td>
                            <td>{{ $poliza->compania->nombre ?? 'N/A' }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $poliza->vigencia_inicio->format('d/m/Y') }}</span>
                                    <small class="text-muted">al</small>
                                    <span>{{ $poliza->vigencia_fin->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td class="text-right">
                                ${{ number_format($poliza->prima_total, 2) }}<br>
                                <small class="text-muted">{{ $poliza->forma_pago }}</small>
                            </td>
                            <td>{{ $poliza->seguro->nombre ?? 'N/A' }}</td>
                            <td>{{ $poliza->ramo->nombre ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if($poliza->ruta_pdf)
                                <a href="{{ asset('storage/' . $poliza->ruta_pdf) }}" target="_blank">
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $poliza->tipo_prima }}</td>
                            <td class="text-center">
                                @if($poliza->vigencia_fin > now())
                                    <span class="badge badge-success">Vigente</span>
                                @else
                                    <span class="badge badge-danger">Vencida</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('polizas.edit', $poliza->id) }}" class="btn btn-sm btn-warning mx-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('polizas.destroy', $poliza->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css" rel="stylesheet">
<style>
    .badge { font-size: 0.85em; padding: 0.5em 0.75em; }
    table.dataTable tbody td { vertical-align: middle !important; }
</style>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    $('#polizasTable').DataTable({
        pageLength: 10,
        dom: "<'row'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-success', exportOptions: { columns: [0,1,2,3,4,6] } },
            { extend: 'pdf',   text: '<i class="fas fa-file-pdf"></i> PDF',     className: 'btn-danger',  exportOptions: { columns: [0,1,2,3,4,6] } },
            { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir',  className: 'btn-dark',    exportOptions: { columns: [0,1,2,3,4,6] } }
        ],
        responsive: true,
        order: [[3, 'desc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json' },
        columnDefs: [ { targets: [7,8,9], orderable: false, className: 'text-center' } ]
    });

    // SweetAlert delete confirmation
    $('#polizasTable').on('click', '.delete-btn', function() {
        const form = $(this).closest('form');
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¡No podrás revertir esto!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@stop
