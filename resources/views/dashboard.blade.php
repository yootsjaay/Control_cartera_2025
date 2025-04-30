@extends('adminlte::page')

@section('title', 'Control Cartera')

@section('content_header')
    <h1>Preevisa ControlPolizas</h1>
@stop

@section('content')
    <div class="container-fluid px-4">
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Total de Pólizas Subidas</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ array_sum($polizasData) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Pólizas con Pagos</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $polizasConPagos ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Total de Pagos</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ array_sum($pagosData) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Pólizas Pendientes</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $polizasPendientes ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Gráfico de Pólizas Subidas por Mes</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="polizasMes" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Gráfico de Pagos Realizados</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="pagosRealizados" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.0/dist/chart.min.js"></script>
    <script>
        const polizasData = @json($polizasData);
        const pagosData = @json($pagosData);

        const ctxPolizas = document.getElementById('polizasMes').getContext('2d');
        const polizasMesChart = new Chart(ctxPolizas, {
            type: 'bar',
            data: {
                labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                datasets: [{
                    label: 'Pólizas Subidas',
                    data: polizasData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    tooltip: { enabled: true }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        const ctxPagos = document.getElementById('pagosRealizados').getContext('2d');
        const pagosRealizadosChart = new Chart(ctxPagos, {
            type: 'line',
            data: {
                labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                datasets: [{
                    label: 'Pagos Realizados',
                    data: pagosData,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    tooltip: { enabled: true }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
@stop
