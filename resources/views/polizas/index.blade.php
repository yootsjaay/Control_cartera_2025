@extends('adminlte::page')

@section('title', 'Pólizas')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1>Listado de Pólizas</h1>
@stop

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <div class="d-flex justify-content-between">
            <h3 class="card-title">Gestión de Pólizas</h3>
            
        </div>
    </div>

    <div class="card-body">
        <!-- Filtros Mejorados -->
        <form id="filtrosForm" method="GET" class="row g-3 mb-4">
            <div class="col-md-2">
                <label for="fecha_inicio" class="form-label">Desde</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control form-control-sm" 
                       value="{{ request('fecha_inicio') }}" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label for="fecha_fin" class="form-label">Hasta</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control form-control-sm" 
                       value="{{ request('fecha_fin') }}" min="{{ request('fecha_inicio') ?? '' }}">
            </div>
            <div class="col-md-2">
                <label for="companiaFilter" class="form-label">Compañía</label>
                <select id="companiaFilter" name="companiaFilter" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($companias as $compania)
                        <option value="{{ $compania->id }}" @selected(request('companiaFilter') == $compania->id)>
                            {{ $compania->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="tipoFilter" class="form-label">Tipo Prima</label>
                <select id="tipoFilter" name="tipoFilter" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($tiposPrima as $tipo)
                        <option value="{{ $tipo }}" @selected(request('tipoFilter') == $tipo)>
                            {{ ucfirst($tipo) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="statusFilter" class="form-label">Estado</label>
                <select id="statusFilter" name="statusFilter" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="vigente" @selected(request('statusFilter') == 'vigente')>Vigente</option>
                    <option value="vencida" @selected(request('statusFilter') == 'vencida')>Vencida</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('polizas.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-broom"></i> Limpiar
                </a>
            </div>
        </form>

        <!-- Tabla de Pólizas -->
        <div class="table-responsive">
            <table id="polizasTable" class="table table-striped table-hover table-sm w-100">
                <thead class="bg-lightblue">
                    <tr>
                        <th>Póliza</th>
                        <th>Cliente</th>
                        <th>Compañía</th>
                        <th>Vigencia</th>
                        <th class="text-end">Monto</th>
                        <th>Seguro</th>
                        <th>Ramo</th>
                        <th class="text-center">Documento</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($polizas as $poliza)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong>#{{ $poliza->numeros_poliza->numero_poliza ?? 'N/A' }}</strong>
                                    <small class="text-muted">Creada: {{ $poliza->created_at->format('d/m/Y') }}</small>
                                </div>
                            </td>
                            <td>{{ Str::limit($poliza->nombre_cliente, 20) }}</td>
                            <td>{{ $poliza->compania->nombre ?? 'N/A' }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $poliza->vigencia_inicio->format('d/m/Y') }}</span>
                                    <small class="text-muted">al</small>
                                    <span>{{ $poliza->vigencia_fin->format('d/m/Y') }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                ${{ number_format($poliza->prima_total, 2) }}
                                @if($poliza->forma_pago)
                                    <br><small class="text-muted">{{ $poliza->forma_pago }}</small>
                                @endif
                            </td>
                            <td>{{ $poliza->seguro->nombre ?? 'N/A' }}</td>
                            <td>{{ $poliza->ramo->nombre ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if($poliza->ruta_pdf)
                                    <a href="{{ Storage::url($poliza->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $poliza->tipo_prima == 'Anual' ? 'info' : 'warning' }}">
                                    {{ $poliza->tipo_prima }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($poliza->vigencia_fin > now())
                                    <span class="badge bg-success">Vigente</span>
                                @else
                                    <span class="badge bg-danger">Vencida</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('polizas.edit', $poliza->id) }}" class="btn btn-outline-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('polizas.destroy', $poliza->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger delete-btn" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">No se encontraron pólizas con los filtros aplicados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
<style>
    .bg-lightblue {
        background-color: #e3f2fd;
    }
    .table-sm td, .table-sm th {
        padding: 0.3rem;
    }
</style>
@stop

@section('js')
<!-- Scripts DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // Configuración DataTable
    const table = $('#polizasTable').DataTable({
        dom: "<'row'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn-success btn-sm',
                exportOptions: { columns: [0,1,2,3,4,5,6,8,9] }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn-danger btn-sm',
                exportOptions: { columns: [0,1,2,3,4,5,6,8,9] }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn-dark btn-sm',
                exportOptions: { columns: [0,1,2,3,4,5,6,8,9] }
            }
        ],

        responsive: true,
        order: [[3, 'desc']], // Ordenar por fecha de vigencia (columna 3)
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
        },
        columnDefs: [
            { targets: [7, 8, 9, 10], orderable: false, className: 'text-center' },
            { targets: 4, className: 'text-end' }
        ],
        initComplete: function() {
            // Verificar que todas las columnas tienen datos
            this.api().columns().every(function() {
                let column = this;
                console.log('Columna', column.index(), 'tiene', column.data().length, 'elementos');
            });
        }
    });

    // Validación de fechas en filtros
    $('#fecha_inicio').change(function() {
        $('#fecha_fin').attr('min', $(this).val());
    });

    // Confirmación de eliminación
    $('#polizasTable').on('click', '.delete-btn', function() {
        const form = $(this).closest('form');
        Swal.fire({
            title: '¿Eliminar Póliza?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Notificaciones
    @if(session('success'))
    Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: '{{ session('success') }}',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        toast: true
    });
    @endif

    @if(session('error'))
    Swal.fire({
        position: 'center',
        icon: 'error',
        title: '{{ session('error') }}',
        showConfirmButton: true
    });
    @endif
});
</script>
@stop