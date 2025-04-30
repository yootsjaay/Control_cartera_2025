<!-- resources/views/polizas/edit.blade.php -->
@extends('adminlte::page')

@section('title', 'Editar Póliza')

@section('content_header')
    <h1>Editar Póliza #{{ $poliza->numeros_poliza->numero_poliza }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="editTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="form-tab" data-bs-toggle="tab" data-bs-target="#form" type="button" role="tab">Datos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="charts-tab" data-bs-toggle="tab" data-bs-target="#charts" type="button" role="tab">Gráficas</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notified-tab" data-bs-toggle="tab" data-bs-target="#notified" type="button" role="tab">Notificaciones</button>
            </li>
        </ul>
        <div class="tab-content mt-3">
            <!-- Form Tab -->
            <div class="tab-pane fade show active" id="form" role="tabpanel">
                @include('polizas._form', ['poliza' => $poliza, 'ramos' => $ramos, 'seguros' => $seguros, 'numeros_polizas' => $numerosPolizas, 'companias' => $companias])
            </div>

            <!-- Charts Tab -->
            <div class="tab-pane fade" id="charts" role="tabpanel">
                <h4>Histórico de Pagos Fraccionados</h4>
                <canvas id="paymentsChart" width="400" height="200"></canvas>
            </div>

            <!-- Notified Tab -->
            <div class="tab-pane fade" id="notified" role="tabpanel">
                <h4>Pólizas Notificadas</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha Notificación</th>
                            <th>Usuario</th>
                            <th>Mensaje</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('paymentsChart').getContext('2d');
        const data = @json(
    $poliza->pagos_fraccionados->map(function($p) {
        return [
            'fecha' => $p->fecha_pago ? $p->fecha_pago->format('Y-m-d') : null,
            'monto' => $p->monto
        ];
    })
);
        const labels = data.map(d => d.fecha);
        const montos = data.map(d => d.monto);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Monto Pago',
                    data: montos,
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    x: { title: { display: true, text: 'Fecha' } },
                    y: { title: { display: true, text: 'Monto' } }
                }
            }
        });
    });
    </script>
@stop
