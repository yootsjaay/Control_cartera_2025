@extends('adminlte::page')

@section('title', 'Seguros')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="m-0 text-dark text-center">Administración de Seguros</h1>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item active"><i class="fas fa-shield-alt"></i> Seguros</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Listado de Seguros Registrados</h3>
            <div class="card-tools">
                <a href="{{ route('seguros.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus-circle mr-2"></i>Nuevo Seguro
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="nombreFilter" class="form-control" placeholder="Buscar por nombre...">
                </div>
                <div class="col-md-4">
                    <select id="companiaFilter" class="form-control">
                        <option value="">Todas las compañías</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->nombre }}">{{ $compania->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="statusFilter" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="con_ramos">Con Ramos</option>
                        <option value="sin_ramos">Sin Ramos</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="segurosTable" class="table table-striped table-hover" style="width:100%">
                    <thead class="bg-primary text-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Compañía</th>
                            <th>Ramos</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($seguros as $seguro)
                            <tr>
                                <td>{{ $seguro->nombre }}</td>
                                <td>{{ $seguro->compania->nombre ?? 'N/A' }}</td>
                                <td>
                                    @if($seguro->ramos->count() > 0)
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-pill badge-info mr-2">
                                                {{ $seguro->ramos->count() }}
                                            </span>
                                            <div class="text-truncate" style="max-width: 200px;">
                                                {{ $seguro->ramos->pluck('nombre_ramo')->join(', ') }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Sin ramos asignados</span>
                                    @endif
                                </td>
                                <td>
                                    @if($seguro->ramos->count() > 0)
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Activo</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Incompleto</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('seguros.edit', $seguro->id) }}" 
                                           class="btn btn-sm btn-warning mx-1"
                                           data-toggle="tooltip"
                                           title="Editar seguro">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('seguros.destroy', $seguro->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-btn mx-1"
                                                    data-toggle="tooltip"
                                                    title="Eliminar seguro">
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
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    .dataTables_wrapper .dt-buttons {
        margin-bottom: 1rem;
    }
    .table thead th {
        vertical-align: middle;
    }
    .badge-pill {
        min-width: 30px;
    }
    .text-truncate {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const table = $('#segurosTable').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn-success',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn-danger',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn-dark',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            }
        ],
        responsive: true,
        autoWidth: false,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
        },
        columnDefs: [
            { targets: [4], orderable: false, className: 'text-center' },
            { targets: [2], width: '30%' },
            { targets: [3], className: 'text-center' }
        ],
        initComplete: function() {
            $('.dataTables_processing').hide();
        }
    });

    // Filtros personalizados
    $('#nombreFilter').on('keyup', function() {
        table.column(0).search(this.value).draw();
    });

    $('#companiaFilter').on('change', function() {
        table.column(1).search(this.value).draw();
    });

    $('#statusFilter').on('change', function() {
        const value = this.value;
        if (value === 'con_ramos') {
            table.column(3).search('Activo').draw();
        } else if (value === 'sin_ramos') {
            table.column(3).search('Incompleto').draw();
        } else {
            table.column(3).search('').draw();
        }
    });

    // Confirmación de eliminación
    $('#segurosTable').on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        
        Swal.fire({
            title: '¿Eliminar seguro?',
            html: `<p>Esta acción eliminará permanentemente:<br>
                  <strong>${$(this).closest('tr').find('td:first').text()}</strong></p>`,
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

    // Tooltips
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        placement: 'top'
    });
});
</script>
@endsection