@extends('adminlte::page')

@section('title', 'Confirmar Pólizas')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Confirmar Datos de Pólizas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('companias.index') }}">Compañías</a></li>
        <li class="breadcrumb-item active">Confirmar</li>
    </ol>

    @if (count($dataExtraida) > 0)
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Datos Extraídos</h3>
            </div>
            <div class="card-body">
                @foreach ($dataExtraida as $index => $datos)
                    <div class="mb-4">
                        <h4 class="text-muted">Póliza {{ $index + 1 }}</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th>Número de Póliza</th>
                                <td>{{ $datos['numero_poliza'] ?? 'No encontrado' }}</td>
                            </tr>
                            <tr>
                                <th>Vigencia Inicio</th>
                                <td>{{ $datos['vigencia_inicio'] ?? 'No encontrado' }}</td>
                            </tr>
                            <tr>
                                <th>Vigencia Fin</th>
                                <td>{{ $datos['vigencia_fin'] ?? 'No encontrado' }}</td>
                            </tr>
                            <tr>
                                <th>Forma de Pago</th>
                                <td>{{ $datos['forma_pago'] ?? 'No encontrado' }}</td>
                            </tr>
                            <tr>
                                <th>Total a Pagar</th>
                                <td>{{ $datos['total_a_pagar'] ?? 'No encontrado' }}</td>
                            </tr>
                            <tr>
                                <th>Cliente</th>
                                <td>{{ $datos['nombre_cliente'] ?? 'No encontrado' }}</td>
                            </tr>
                            <tr>
                                <th>RFC</th>
                                <td>{{ $datos['rfc'] ?? 'No encontrado' }}</td>
                            </tr>
                            <tr>
                                <th>Archivo PDF</th>
                                <td>
                                    <a href="{{ asset("storage/{$datos['archivo_pdf']}") }}" target="_blank">
                                        Ver archivo
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endforeach
            </div>
            <div class="card-footer">
                <form action="{{ route('polizas.guardar') }}" method="POST">
                    @csrf
                    <input type="hidden" name="datos" value="{{ json_encode($dataExtraida) }}">
                    <button type="submit" class="btn btn-success">Confirmar y Guardar</button>
                    <a href="{{ route('polizas.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-danger">
            <strong>Error:</strong> No se encontraron datos para confirmar.
        </div>
    @endif
</div>
@endsection
