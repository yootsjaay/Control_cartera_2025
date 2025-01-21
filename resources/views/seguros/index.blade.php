@extends('adminlte::page')

@section('title', 'Seguros')

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
    <h1 class="mt-4 text-center">Seguros</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Seguros</li>
    </ol>
    <div class="mb-4">
        <a href="{{ route('seguros.create') }}">
            <button type="button" class="btn btn-primary">Registrar Nuevo Seguro</button>
        </a>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Seguros
        </div>
        <div class="card-body">
        <table id="datatablesSimple" class="table table-striped" style="width:100%">        <thead>
        <tr>
                <th>Nombre del Seguro</th>
                <th>Compañía</th>
                <th>Ramos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($seguros as $seguro)
                <tr>
                    <td>{{ $seguro->nombre }}</td>
                    <td>{{ $seguro->compania->nombre }}</td>
                    <td>
                        <ul>
                            @foreach($seguro->ramos as $ramo)
                                <li>{{ $ramo->nombre_ramo }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>
                        <a href="{{ route('seguros.edit', $seguro->id) }}">Editar</a>
                        <form action="{{ route('seguros.destroy', $seguro->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

        </div>
    </div>
</div>
@stop 


@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .table-header{
            white-space: nowrap; /* Evita que el texto se divida en varias líneas */
            overflow: hidden;   /* Oculta el texto que sobrepase el ancho */
            text-overflow: ellipsis; /* Añade "..." al final del texto truncado */
            max-width: 150px;   /* Ajusta el ancho máximo según tus necesidades */
 
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
    <script>
        $(document).ready(function() {
            $('#datatablesSimple').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-MX.json',
                },
            });
        });
    </script>
@stop
