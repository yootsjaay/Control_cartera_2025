@extends('adminlte::page')

@section('title', 'Editar Póliza')

@section('content_header')
    <h1>Editar Póliza #{{ $poliza->numeros_poliza->numero_poliza }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="editTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="form-tab" data-bs-toggle="tab" data-bs-target="#form" type="button" role="tab">Datos</button>
            </li>
           
        </ul>
        
        <div class="tab-content mt-3">
            <!-- Form Tab -->
            <div class="tab-pane fade show active" id="form" role="tabpanel">
                @include('polizas._form', [
                    'poliza' => $poliza,
                    'ramos' => $ramos,
                    'seguros' => $seguros,
                    'numeros_polizas' => $numeros_polizas,
                    'companias' => $companias
                ])
            </div>

            <!-- Charts Tab -->
            <div class="tab-pane fade" id="charts" role="tabpanel">
                @if($poliza->pagos_fraccionados->isNotEmpty())
                    <h4>Histórico de Pagos Fraccionados</h4>
                    <canvas id="paymentsChart" width="400" height="200"></canvas>
                @else
                    <div class="alert alert-info">No hay pagos registrados</div>
                @endif
            </div>
        </div>
        </div>
        </div>
        </div>
</div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @if($poliza->pagos_fraccionados->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('paymentsChart').getContext('2d');
            const data = @json($poliza->pagos_fraccionados->map(function($p) {
                return [
                    'fecha' => $p->fecha_pago ? $p->fecha_pago->format('Y-m-d') : null,
                    'monto' => $p->monto
                ];
            }));
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.fecha),
                    datasets: [{
                        label: 'Monto Pagado',
                        data: data.map(d => d.monto),
                        borderColor: '#4e73df',
                        tension: 0.4
                    }]
                },
                options: {
                    scales: {
                        x: { title: { display: true, text: 'Fecha' } },
                        y: { 
                            title: { display: true, text: 'Monto' },
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
    @endif
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Carga dinámica de Ramos y Compañías
            const seguroSelect = document.getElementById('seguro_id');
            const ramoSelect = document.getElementById('ramo_id');
            const companiaSelect = document.getElementById('compania_id');
            
            async function cargarDependientes(seguroId) {
                try {
                    const response = await fetch(`/polizas/obtener-datos-seguro/${seguroId}`);
                    const data = await response.json();
                    
                    // Actualizar Ramos
                    ramoSelect.innerHTML = '<option value="">Seleccione...</option>' + 
                        data.ramos.map(ramo => 
                            `<option value="${ramo.id}" ${ramo.id == @json($poliza->ramo_id) ? 'selected' : ''}>
                                ${ramo.nombre}
                            </option>`
                        ).join('');
                    
                    // Actualizar Compañías
                    companiaSelect.innerHTML = '<option value="">Seleccione...</option>' + 
                        data.companias.map(compania => 
                            `<option value="${compania.id}" ${compania.id == @json($poliza->compania_id) ? 'selected' : ''}>
                                ${compania.nombre}
                            </option>`
                        ).join('');
                } catch (error) {
                    console.error('Error:', error);
                }
            }
            
            // Cargar datos iniciales si hay un seguro seleccionado
            if(seguroSelect.value) {
                cargarDependientes(seguroSelect.value);
            }
            
            // Evento change
            seguroSelect.addEventListener('change', function() {
                if(this.value) {
                    cargarDependientes(this.value);
                }
            });
        });
    </script>
@stop