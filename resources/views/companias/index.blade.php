@extends('adminlte::page')

@section('title', 'Compañías')

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="m-0 text-dark text-center">Administración de Compañías</h1>
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item active"><i class="fas fa-building"></i> Compañías</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Listado de Compañías Registradas</h3>
            <div class="card-tools">
                <a href="{{ route('companias.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus-circle mr-2"></i>Nueva Compañía
                </a>
            </div>
        </div>

        <div class="card-body">
         
            <div class="table-responsive">
                <table id="companiasTable" class="table table-striped table-hover" style="width:100%">
                    <thead class="bg-primary text-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Slug</th>
                            <th>Clase</th>
                            <th>Fecha de Creación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companias as $compania)
                            <tr>
                                <td>{{ $compania->nombre }}</td>
                                <td>{{ $compania->slug }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $compania->clase)) }}</td>
                                <td>{{ $compania->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('companias.edit', $compania->id) }}" 
                                           class="btn btn-sm btn-warning mx-1"
                                           data-toggle="tooltip"
                                           title="Editar compañía">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('companias.destroy', $compania->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-btn mx-1"
                                                    data-toggle="tooltip"
                                                    title="Eliminar compañía">
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
        const table = $('#companiasTable').DataTable({
            dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn-success',
                    exportOptions: { columns: [0, 1, 2, 3] }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn-danger',
                    exportOptions: { columns: [0, 1, 2, 3] }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn-dark',
                    exportOptions: { columns: [0, 1, 2, 3] }
                }
            ],
            responsive: true,
            autoWidth: false,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
            },
            columnDefs: [
                { targets: 4, orderable: false, className: 'text-center' }, // Acciones
                { targets: 3, className: 'text-center' } // Fecha de Creación
            ],
            order: [[3, 'desc']], // Ordenar por fecha de creación descendente
            initComplete: function() {
                $('.dataTables_processing').hide();
            }
        });

        // Filtros personalizados
        $('#nombreFilter').on('keyup', function() {
            table.column(0).search(this.value).draw();
        });

        $('#claseFilter').on('change', function() {
            table.column(2).search(this.value).draw();
        });

        // Confirmación de eliminación
        $('#companiasTable').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            
            Swal.fire({
                title: '¿Eliminar compañía?',
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