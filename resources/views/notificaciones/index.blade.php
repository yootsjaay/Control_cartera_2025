{{-- resources/views/notifications/history.blade.php --}}
@extends('adminlte::page')

@section('title', 'Panel de Pólizas y Pagos')

@section('content_header')
    <h1 class="m-0 text-dark">Panel de Pólizas y Pagos</h1>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Pólizas Próximas a Vencer -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Pólizas Próximas a Vencer
                </h3>
            </div>
            <div class="card-body">
                @if($polizasPorVencer->isEmpty())
                    <div class="alert alert-info">No hay pólizas próximas a vencer.</div>
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
                                    <th>Días Restantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($polizasPorVencer as $poliza)
                                <tr>
                                    <td>{{ $poliza->numeros_poliza->numero_poliza }}</td>
                                    <td>{{ $poliza->nombre_cliente }}</td>
                                    <td>{{ $poliza->compania->nombre }}</td>
                                    <td>{{ $poliza->ramo->nombre }}</td>
                                    <td>{{ $poliza->vigencia_fin->isoFormat('LL') }}</td>
                                    <td>
                                        <span class="badge badge-warning">{{ $poliza->dias_restantes }} días</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('polizas.show', $poliza->id) }}" 
                                           class="btn btn-sm btn-primary"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                 
                @endif
            </div>
        </div>

        <!-- Pólizas Vencidas -->
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-times-circle mr-2"></i>
                    Pólizas Vencidas
                </h3>
            </div>
            <div class="card-body">
                @if($polizasVencidas->isEmpty())
                    <div class="alert alert-info">No hay pólizas vencidas.</div>
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
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($polizasVencidas as $poliza)
                                <tr>
                                    <td>{{ $poliza->numeros_poliza->numero_poliza }}</td>
                                    <td>{{ $poliza->nombre_cliente }}</td>
                                    <td>{{ $poliza->compania->nombre }}</td>
                                    <td>{{ $poliza->ramo->nombre }}</td>
                                    <td>{{ $poliza->vigencia_fin->isoFormat('LL') }}</td>
                                    <td>
                                        <a href="{{ route('polizas.show', $poliza->id) }}" 
                                           class="btn btn-sm btn-primary"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="" 
                                           class="btn btn-sm btn-success"
                                           title="Renovar póliza">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                   
                @endif
            </div>
        </div>

        <!-- Pagos Pendientes -->
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Pagos Pendientes
                </h3>
            </div>
            <div class="card-body">
                @if($pagosPendientes->isEmpty())
                    <div class="alert alert-info">No hay pagos pendientes.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nº Póliza</th>
                                    <th>Cliente</th>
                                    <th>Nº Recibo</th>
                                    <th>Importe</th>
                                    <th>Fecha Límite</th>
                                    <th>Días Restantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pagosPendientes as $pago)
                                <tr>
                                    <td>{{ $pago->poliza->numeros_poliza->numero_poliza }}</td>
                                    <td>{{ $pago->poliza->nombre_cliente }}</td>
                                    <td>{{ $pago->numero_recibo }}</td>
                                    <td class="text-danger font-weight-bold">
                                        @money($pago->importe)
                                    </td>
                                    <td>{{ $pago->fecha_limite_pago->isoFormat('LL') }}</td>
                                    <td>
                                        <span class="badge badge-danger">{{ $pago->dias_restantes }} días</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('pagos.show', $pago->id) }}" 
                                           class="btn btn-sm btn-primary"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('pagos.create', $pago->id) }}" 
                                           class="btn btn-sm btn-success"
                                           title="Registrar pago">
                                            <i class="fas fa-dollar-sign"></i>
                                        </a>
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
    </script>
@stop