@extends('adminlte::page')

@section('title', 'Lista de Pólizas')

@section('content_header')
    <!-- Mensaje de éxito o error -->

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  @section('content')
@stop


<div class="card">
    <div class="card-header text-center">
        <h1>Lista de Pólizas</h1>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="listaPolizas" class="table table-striped nowrap" style="width:100%">
                <thead class="">
                    <tr>
                        <th>Id</th>
                        <th>Cliente</th>
                        <th>Número de Póliza</th>
                        <th>Compañía</th>
                        <th>Forma de Pago</th>
                        <th>Total a Pagar</th>
                        <th>Archivo</th>
                        <th>Fecha de Inicio</th>
                        <th>Fecha de Fin</th>
                        <th>Tipo Seguro</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($polizas as $poliza)
                        <tr>
                            <td>{{ $poliza->id }}</td>
                            <td>{{ $poliza->cliente->nombre_completo ?? 'N/A' }}</td>
                            <td>{{ $poliza->numero_poliza }}</td>
                            <td>{{ $poliza->compania->nombre ?? 'N/A' }}</td>
                            <td>{{ $poliza->forma_pago ?? 'N/A' }}</td>
                            <td>${{ number_format($poliza->total_a_pagar, 2) }}</td>
                            <td>
                                @if ($poliza->archivo_pdf)
                                    <a href="{{ asset('storage/' . $poliza->pdf) }}" target="_blank">Ver PDF</a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $poliza->vigencia_inicio ? $poliza->vigencia_inicio->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $poliza->vigencia_fin ? $poliza->vigencia_fin->format('d/m/Y') : 'N/A' }}</td>
                            
                            <!-- Modal dinámico para pagos subsecuentes -->
                            <td>

                            </td>

                            <td>{{ $poliza->created_at->format('d/m/Y') }}</td>

                            <!-- Acciones con dropdown -->
                            <td class="text-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-edit"></i> Opciones
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('polizas.edit', $poliza->id) }}">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('polizas.destroy', $poliza->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('¿Estás seguro de eliminar esta póliza?')">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.css">
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
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.js"></script>

    <script>
        $(document).ready(function() {
            $('#listaPolizas').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-MX.json',
                },
            });
        });
    </script>
@stop
