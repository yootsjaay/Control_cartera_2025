@extends('adminlte::page')

@section('title', 'Crear Compañía')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Compañía</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('companias.index') }}">Compañías</a></li>
        <li class="breadcrumb-item active">Crear</li>
    </ol>

    <div class="card mb-4 mx-auto" style="max-width: 700px;">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Crear Compañía
        </div>
        <div class="card-body">
            <form action="{{ route('companias.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="clase">Pertenece a</label>
                    <select name="clase" id="clase" class="form-control" required>
                        <option value="" disabled selected>Elige una compañía</option>
                        @foreach ($clases as $slug => $servicio)
                            <option value="{{ $slug }}">{{ ucfirst(str_replace('-seguros', '', $slug)) }}</option>
                        @endforeach
                    </select>
                    @error('clase')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary mt-3">Crear Compañía</button>
                    <a href="{{ route('companias.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
@endsection