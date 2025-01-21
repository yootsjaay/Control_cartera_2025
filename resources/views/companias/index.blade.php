@extends('adminlte::page')

@section('title', 'Lista Companias')
@section('extra-styles')
    <!-- Incluir estilos adicionales específicos para esta vista -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Compañía</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item active">Compañía</li>
    </ol>
    <div class="mb-4">
        <a href="{{ route('companias.create') }}">
            <button type="submit" class="btn btn-primary">Registrar Compañía</button>
        </a>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Compañía
        </div>
        <div class="card-body">
            <table id="listaCompania" class="table table-striped table-bordered">
                <thead>
                    <tr>
                    <th>Nombre</th>
                    <th>Fecha de Creación</th>
                    <th>Fecha de Actualización</th>
                    <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($companias as $compania)
            <tr>
                <td>{{ $compania->nombre }}</td>
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
        @endforeach
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
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-MX.json',
                },
            });
        });
   </script>
@endsection