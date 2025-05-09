@extends('adminlte::page')

@section('title', 'Listado de Usuarios')

@section('content_header')
    <h1>Listado de Usuarios</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            @if(session('token'))
                <div class="mt-2">
                    <strong>Token:</strong> 
                    <code class="bg-light p-1 rounded">{{ session('token') }}</code>
                    <button id="copyToken" class="btn btn-sm btn-outline-secondary ml-2">Copiar</button>
                    <small class="text-muted d-block">¡Guárdalo ahora! No se mostrará nuevamente.</small>
                </div>
            @endif
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <a href="{{ route('users.create') }}" class="btn btn-primary">Nuevo Usuario</a>
        </div>
        <div class="card-body">
            <table id="users-table" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Grupo</th>
                        <th>Roles</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->group->nombre ?? 'Sin grupo' }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-primary">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Grupo</th>
                        <th>Roles</th>
                        <th>Acciones</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@stop

@section('css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
@stop

@section('js')
    <!-- jQuery, DataTables y plugins -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicialización de DataTable
            var table = $('#users-table').DataTable({
                responsive: true,
                dom: '<"row"<"col-md-6"B><"col-md-6"f>>' +
                     '<"row"<"col-md-12"tr>>' +
                     '<"row"<"col-md-5"i><"col-md-7"p>>',
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Copiar',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-info'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-eye"></i> Columnas',
                        className: 'btn btn-warning'
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                columnDefs: [
                    {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Copiar token al portapapeles
            $('#copyToken').on('click', function() {
                const token = "{{ session('token') }}";
                navigator.clipboard.writeText(token).then(function() {
                    toastr.success('Token copiado al portapapeles');
                }).catch(function() {
                    toastr.error('Error al copiar el token');
                });
            });
        });
    </script>
@stop