@extends('adminlte::page')

@section('title', 'Editar Compañía')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Compañía</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('companias.index') }}">Compañía</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>

    <div class="card mb-4" style="max-width: 700px; margin: 0 auto;">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Editar Compañía
        </div>
        <div class="card-body">
            <div class="form-container">
                <form action="{{ route('companias.update', $compania->id) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Método para actualizar -->

                    <!-- Nombre -->
                    <div class="form-group mb-3">
                        <label for="nombre">Nombre de la Compañía</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $compania->nombre) }}" required>
                        @if ($errors->has('nombre'))
                            <span class="text-danger">{{ $errors->first('nombre') }}</span>
                        @endif
                    </div>

                    <!-- Slug -->
                    <div class="form-group mb-3">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $compania->slug) }}" required>
                        @if ($errors->has('slug'))
                            <span class="text-danger">{{ $errors->first('slug') }}</span>
                        @endif
                    </div>

                    <!-- Clase -->
                    <div class="form-group mb-3">
                        <label for="clase">Clase</label>
                        <select name="clase" id="clase" class="form-control" required>
                            <option value="" disabled>Seleccione una clase</option>
                            @foreach ($clases as $clase => $nombre)
                                <option value="{{ $clase }}" {{ old('clase', $compania->clase) == $clase ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('clase'))
                            <span class="text-danger">{{ $errors->first('clase') }}</span>
                        @endif
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-success mt-3">Actualizar Compañía</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
@stop

@section('js')
    <script>
        console.log('Página lista.');
    </script>
@stop
