<!-- resources/views/polizas/_form.blade.php -->
@php
    $isEdit = isset($poliza);
@endphp

<form 
    action="{{ $isEdit ? route('polizas.update', $poliza->id) : route('polizas.store') }}" 
    method="POST" 
    enctype="multipart/form-data"
>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-4 form-group">
            <label for="ramo_id">Ramo</label>
            <select name="ramo_id" id="ramo_id" class="form-control" required>
                <option value="">Seleccione</option>
                @foreach($ramos as $id => $nombre)
                    <option value="{{ $id }}" {{ old('ramo_id', $poliza->ramo_id ?? '') == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 form-group">
            <label for="seguro_id">Seguro</label>
            <select name="seguro_id" id="seguro_id" class="form-control" required>
                <option value="">Seleccione</option>
                @foreach($seguros as $id => $nombre)
                    <option value="{{ $id }}" {{ old('seguro_id', $poliza->seguro_id ?? '') == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 form-group">
            <label for="numero_poliza_id">Número de Póliza</label>
            <select name="numero_poliza_id" id="numero_poliza_id" class="form-control" required>
                <option value="">Seleccione</option>
                @foreach($numerosPolizas as $id => $numero)
                    <option value="{{ $id }}" {{ old('numero_poliza_id', $poliza->numero_poliza_id ?? '') == $id ? 'selected' : '' }}>#{{ $numero }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4 form-group">
            <label for="compania_id">Compañía</label>
            <select name="compania_id" id="compania_id" class="form-control" required>
                <option value="">Seleccione</option>
                @foreach($companias as $id => $nombre)
                    <option value="{{ $id }}" {{ old('compania_id', $poliza->compania_id ?? '') == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 form-group">
            <label for="nombre_cliente">Nombre Cliente</label>
            <input type="text" name="nombre_cliente" id="nombre_cliente" class="form-control" value="{{ old('nombre_cliente', $poliza->nombre_cliente ?? '') }}" required>
        </div>

        <div class="col-md-4 form-group">
            <label for="tipo_prima">Tipo de Prima</label>
            <select name="tipo_prima" id="tipo_prima" class="form-control" required>
                <option value="Anual" {{ old('tipo_prima', $poliza->tipo_prima ?? '') == 'Anual' ? 'selected' : '' }}>Anual</option>
                <option value="Fraccionado" {{ old('tipo_prima', $poliza->tipo_prima ?? '') == 'Fraccionado' ? 'selected' : '' }}>Fraccionado</option>
            </select>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3 form-group">
            <label for="vigencia_inicio">Vigencia Inicio</label>
            <input type="date" name="vigencia_inicio" id="vigencia_inicio" class="form-control" value="{{ old('vigencia_inicio', isset($poliza) ? $poliza->vigencia_inicio->format('Y-m-d') : '') }}" required>
        </div>
        <div class="col-md-3 form-group">
            <label for="vigencia_fin">Vigencia Fin</label>
            <input type="date" name="vigencia_fin" id="vigencia_fin" class="form-control" value="{{ old('vigencia_fin', isset($poliza) ? $poliza->vigencia_fin->format('Y-m-d') : '') }}" required>
        </div>
        <div class="col-md-3 form-group">
            <label for="prima_total">Prima Total</label>
            <input type="number" step="0.01" name="prima_total" id="prima_total" class="form-control text-right" value="{{ old('prima_total', $poliza->prima_total ?? '') }}" required>
        </div>
        <div class="col-md-3 form-group">
            <label for="forma_pago">Forma de Pago</label>
            <input type="text" name="forma_pago" id="forma_pago" class="form-control" value="{{ old('forma_pago', $poliza->forma_pago ?? '') }}">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6 form-group">
            <label for="primer_pago_fraccionado">Primer Pago Fraccionado</label>
            <input type="date" name="primer_pago_fraccionado" id="primer_pago_fraccionado" class="form-control" value="{{ old('primer_pago_fraccionado', isset($poliza) && $poliza->primer_pago_fraccionado ? $poliza->primer_pago_fraccionado->format('Y-m-d') : '') }}">
        </div>
        <div class="col-md-6 form-group">
            <label for="ruta_pdf">Documento (PDF)</label>
            <input type="file" name="ruta_pdf" id="ruta_pdf" class="form-control" accept="application/pdf">
            @if(isset($poliza) && $poliza->ruta_pdf)
                <small><a href="{{ asset('storage/' . $poliza->ruta_pdf) }}" target="_blank">Ver PDF actual</a></small>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-success">{{ $isEdit ? 'Actualizar' : 'Guardar' }}</button>
        <a href="{{ route('polizas.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
