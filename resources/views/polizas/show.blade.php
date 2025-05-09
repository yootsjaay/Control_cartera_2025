@extends('adminlte::page')

@section('title', 'Pólizas')
@section('content')
    <h1>Póliza: {{ $poliza->numero_poliza_id }}</h1>

    <div class="card">
        <div class="card-body">
            <p><strong>Cliente:</strong> {{ $poliza->nombre_cliente }}</p>
            <p><strong>Ramo:</strong> {{ $poliza->ramo->nombre }}</p>
            <p><strong>Seguro:</strong> {{ $poliza->seguro->nombre }}</p>
            <p><strong>Compañía:</strong> {{ $poliza->compania->nombre }}</p>
            <p><strong>Vigencia Inicio:</strong> {{ $poliza->vigencia_inicio }}</p>
            <p><strong>Vigencia Fin:</strong> {{ $poliza->vigencia_fin }}</p>

            <h2>Pagos Fraccionados</h2>
            <ul>
                @foreach($poliza->pagos_fraccionados as $pago)
                    <li>Monto: {{ $pago->monto }}, Fecha de Vencimiento: {{ $pago->fecha_vencimiento }}</li>
                @endforeach
            </ul>

            @if($poliza->ruta_pdf)
               <a href="{{ Storage::url($poliza->ruta_pdf) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
            @endif
        </div>
    </div>
@endsection
