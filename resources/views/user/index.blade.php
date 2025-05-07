@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
    <h1>Usuarios Registrados</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Alertas -->
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    @if(session('token'))
    <div class="alert alert-info">
        <strong>Token de acceso:</strong>
        <span style="display:inline-block; background:#f1f1f1; padding:5px 10px; border-radius:5px; font-family:monospace; color:#333;">
            {{ session('token') }}
        </span>
        <br>
        <small>Guárdalo bien, este token solo se muestra una vez.</small>
    </div>
@endif



    <!-- Botón nuevo -->
    <div class="mb-3 text-right">
        <a href="{{ route('user.create') }}" class="btn btn-primary">
            Nuevo Usuario
        </a>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body">
            <table id="usuarios-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Grupo</th>
                        <th>Roles</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->group->nombre ?? 'Sin grupo' }}</td>
                        <td>
                            @foreach($user->getRoleNames() as $role)
                            <span class="badge badge-secondary">{{ $role }}</span>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('user.edit', $user->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay usuarios registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function () {
        $('#usuarios-table').DataTable({
           
            responsive: true
        });
    });
</script>
@stop
