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
                    <i class="fas fa-plus-circle mr-2"></i>Nueva Póliza
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
               
                <div class="col-md-3">
                    <select id="companiaFilter" class="form-control">
                        <option value="">Todas las compañías</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->nombre }}">{{ $compania->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="vigente">Vigentes</option>
                        <option value="vencida">Vencidas</option>
                    </select>
                </div>
                <div class="col-md-3">
    <select id="tipoFilter" class="form-control">
        <option value="">Todos los tipos</option>
        @foreach($polizas as $id => $nombre)
            <option value="{{ $id }}">{{ $nombre }}</option>
        @endforeach
    </select>
</div>
            </div>

            <div class="table-responsive">
                <table id="polizasTable" class="table table-striped table-hover" style="width:100%">
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
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($polizas as $poliza)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong>#{{ $poliza->numero_poliza }}</strong>
                                        <small class="text-muted">{{ $poliza->tipo_seguro }}</small>
                                        <small class="text-muted">Creada: {{ $poliza->created_at->format('d/m/Y') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-40 symbol-light-primary mr-3">
                                            <i class="fas fa-user-tie fa-lg text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="text-dark-75 font-weight-bolder">{{ $poliza->cliente->nombre_completo ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $poliza->cliente->rfc ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                      
                                        {{ $poliza->compania->nombre ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark-75">{{ $poliza->vigencia_inicio?->format('d/m/Y') ?? 'N/A' }}</span>
                                        <span class="text-muted small">al</span>
                                        <span class="text-dark-75">{{ $poliza->vigencia_fin?->format('d/m/Y') ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <span class="text-dark-75 font-weight-bolder">${{ number_format($poliza->total_a_pagar, 2) }}</span>
                                    <small class="text-muted d-block">{{ $poliza->forma_pago ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $poliza->seguro->nombre ?? 'N/A' }}</td>
                                <td>{{ $poliza->seguro->ramo->nombre ?? 'N/A' }}</td>
                                <td class="text-center">
                                    @if ($poliza->archivo_pdf)
                                        <a href="{{ asset('storage/' . $poliza->archivo_pdf) }}" 
                                           class="btn btn-link text-primary" 
                                           target="_blank"
                                           data-toggle="tooltip"
                                           title="Descargar PDF">
                                            <i class="fas fa-file-pdf fa-2x"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($poliza->vigencia_fin > now())
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Vigente</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Vencida</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('polizas.edit', $poliza->id) }}" 
                                           class="btn btn-sm btn-warning mx-1"
                                           data-toggle="tooltip"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('polizas.destroy', $poliza->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-btn mx-1"
                                                    data-toggle="tooltip"
                                                    title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css">
<style>
    .symbol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .symbol-40 {
        width: 40px;
        height: 40px;
    }
    .img-size-32 {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }
    .badge {
        font-size: 0.85em;
        padding: 0.5em 0.75em;
    }
    table.dataTable tbody td {
        vertical-align: middle !important;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const table = $('#polizasTable').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn-success',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 6]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn-danger',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 6]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn-dark',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 6]
                }
            }
        ],
        responsive: true,
        order: [[3, 'desc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
        },
        columnDefs: [
            { targets: [7], orderable: false, className: 'text-center' },
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: 1 }
        ]
    });

    // Filtros personalizados
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#companiaFilter').on('change', function() {
        table.column(2).search(this.value).draw();
    });

    $('#statusFilter').on('change', function() {
        const value = this.value;
        table.column(8).search(value === 'vigente' ? 'Vigente' : 'Vencida').draw(); // Asegúrate de que la columna 8 sea la correcta
    });

    $('#tipoFilter').on('change', function() {
        const value = this.value;
        table.column(5).search(value).draw(); // Asegúrate de que la columna 5 sea la correcta
    });

    // Confirmación de eliminación
    $('#polizasTable').on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const polizaNumero = $(this).closest('tr').find('td:first').text();
        
        Swal.fire({
            title: '¿Eliminar póliza?',
            html: `<p>Esta acción eliminará permanentemente la póliza:<br>
                  <strong>${polizaNumero}</strong></p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return form.submit();
            }
        });
    });
 // Notificación de "poliza Eliminado"
 @if (session('delete'))
        Swal.fire({
            title: '¡Éxito!',
            text: 'Poliza Eliminado',
            icon: 'delete',
            confirmButtonText: 'Aceptar',
            timer: 3000, // Se cierra automáticamente después de 3 segundos
            timerProgressBar: true
        });
    @endif
    // Notificación de "Poliza Credo"
    @if (session('success'))
        Swal.fire({
            title: '¡Éxito!',
            text: 'Poliza creado correctamente',
            icon: 'success',
            confirmButtonText: 'Aceptar',
            timer: 3000, // Se cierra automáticamente después de 3 segundos
            timerProgressBar: true
        });
    @endif

    // Tooltips
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        placement: 'top'
    });
});
</script>
@endsection