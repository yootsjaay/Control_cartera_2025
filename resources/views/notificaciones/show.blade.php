{{-- resources/views/notifications/history.blade.php --}}
@extends('adminlte::page')

@section('title', 'Panel de Pólizas y Pagos')

@section('content_header')
    <h1 class="m-0 text-dark">Panel de Pólizas y Pagos</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-times mr-2"></i>
                    Pólizas Vencidas
                </h3>
            </div>
            <div class="card-body">
                @if($polizasVencidas->isEmpty())
                    <div class="alert alert-warning">No hay pólizas vencidas.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nº Póliza</th>
                                    <th>Cliente</th>
                                    <th>Compañía</th>
                                    <th>Ramo</th>
                                    <th>Vencimiento</th>
                                    <th>Días Vencidos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($polizasVencidas as $poliza)
                                <tr>
                                    <td>{{ $poliza->numeros_poliza->numero_poliza ?? 'N/A' }}</td>
                                    <td>{{ $poliza->nombre_cliente }}</td>
                                    <td>{{ $poliza->compania->nombre ?? 'N/A' }}</td>
                                    <td>{{ $poliza->ramo->nombre ?? 'N/A' }}</td>
                                    <td>{{ $poliza->vigencia_fin->format('d/m/Y') }}</td>
                                    <td>{{ Carbon\Carbon::now()->diffInDays($poliza->vigencia_fin) }}</td>
                                    <td>
                                        <a href="{{ route('polizas.show', $poliza->id) }}"
                                           class="btn btn-sm btn-info"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('polizas.renovar', $poliza->id) }}"  class="btn btn-sm btn-success"
                                           title="Renovar Póliza">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                        <button class="btn btn-sm btn-warning"
                                                title="Enviar Recordatorio"
                                                onclick="enviarRecordatorio({{ $poliza->id }})">
                                            <i class="fas fa-bell"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('table').DataTable({
                "paging": false,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
                }
            });
        });

        function enviarRecordatorio(polizaId) {
            //  Implementa la lógica para enviar un recordatorio (AJAX, etc.)
            alert('Recordatorio enviado para la póliza ' + polizaId); //  Solo un ejemplo
        }
    </script>
@stop