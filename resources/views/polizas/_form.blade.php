@php
    $isEdit = isset($poliza);
@endphp

<form action="{{ $isEdit ? route('polizas.update', $poliza->id) : route('polizas.store') }}" 
      method="POST" 
      enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <!-- Ramo -->
        <div class="col-md-4 form-group">
            <label for="ramo_id">Ramo <span class="text-danger">*</span></label>
            <select name="ramo_id" id="ramo_id" class="form-control @error('ramo_id') is-invalid @enderror" required>
                <option value="">Seleccione...</option>
                @foreach($ramos as $id => $nombre)
                    <option value="{{ $id }}" {{ old('ramo_id', $poliza->ramo_id ?? '') == $id ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('ramo_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Seguro -->
        <div class="col-md-4 form-group">
            <label for="seguro_id">Seguro <span class="text-danger">*</span></label>
            <select name="seguro_id" id="seguro_id" class="form-control @error('seguro_id') is-invalid @enderror" required>
                <option value="">Seleccione...</option>
                @foreach($seguros as $id => $nombre)
                    <option value="{{ $id }}" {{ old('seguro_id', $poliza->seguro_id ?? '') == $id ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('seguro_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


            <!-- Número de Póliza -->
        <div class="col-md-4 form-group">
            <label for="numero_poliza">Número Póliza <span class="text-danger">*</span></label>
            <input type="text" 
                name="numero_poliza" 
                id="numero_poliza"
                class="form-control @error('numero_poliza') is-invalid @enderror" 
                value="{{ old('numero_poliza', $poliza->numeros_poliza->numero_poliza ?? '') }}"
                required
                placeholder="Ingrese el número de póliza">
            @error('numero_poliza')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

    <div class="row mt-3">
        <!-- Compañía -->
        <div class="col-md-4 form-group">
            <label for="compania_id">Compañía <span class="text-danger">*</span></label>
            <select name="compania_id" id="compania_id" class="form-control @error('compania_id') is-invalid @enderror" required>
                <option value="">Seleccione...</option>
                @foreach($companias as $id => $nombre)
                    <option value="{{ $id }}" {{ old('compania_id', $poliza->compania_id ?? '') == $id ? 'selected' : '' }}>
                        {{ $nombre }}
                    </option>
                @endforeach
            </select>
            @error('compania_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Cliente -->
        <div class="col-md-4 form-group">
            <label for="nombre_cliente">Nombre Cliente <span class="text-danger">*</span></label>
            <input type="text" name="nombre_cliente" id="nombre_cliente" 
                   class="form-control @error('nombre_cliente') is-invalid @enderror" 
                   value="{{ old('nombre_cliente', $poliza->nombre_cliente ?? '') }}" required>
            @error('nombre_cliente')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Tipo Prima -->
        <div class="col-md-4 form-group">
            <label for="tipo_prima">Tipo de Prima <span class="text-danger">*</span></label>
            <select name="tipo_prima" id="tipo_prima" class="form-control @error('tipo_prima') is-invalid @enderror" required>
                <option value="Anual" {{ old('tipo_prima', $poliza->tipo_prima ?? '') == 'Anual' ? 'selected' : '' }}>Anual</option>
                <option value="Fraccionado" {{ old('tipo_prima', $poliza->tipo_prima ?? '') == 'Fraccionado' ? 'selected' : '' }}>Fraccionado</option>
            </select>
            @error('tipo_prima')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row mt-3">
        <!-- Vigencia -->
        <div class="col-md-3 form-group">
            <label for="vigencia_inicio">Vigencia Inicio <span class="text-danger">*</span></label>
            <input type="date" name="vigencia_inicio" id="vigencia_inicio" 
                   class="form-control @error('vigencia_inicio') is-invalid @enderror" 
                   value="{{ old('vigencia_inicio', $poliza->vigencia_inicio->format('Y-m-d') ?? '') }}" required>
            @error('vigencia_inicio')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3 form-group">
            <label for="vigencia_fin">Vigencia Fin <span class="text-danger">*</span></label>
            <input type="date" name="vigencia_fin" id="vigencia_fin" 
                   class="form-control @error('vigencia_fin') is-invalid @enderror" 
                   value="{{ old('vigencia_fin', $poliza->vigencia_fin->format('Y-m-d') ?? '') }}" required>
            @error('vigencia_fin')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Prima Total -->
        <div class="col-md-3 form-group">
            <label for="prima_total">Prima Total <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" name="prima_total" id="prima_total" 
                       class="form-control @error('prima_total') is-invalid @enderror text-end" 
                       value="{{ old('prima_total', $poliza->prima_total ?? '') }}" required>
            </div>
            @error('prima_total')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Forma Pago -->
        <div class="col-md-3 form-group">
            <label for="forma_pago">Forma de Pago <span class="text-danger">*</span></label>
            <select name="forma_pago" id="forma_pago" class="form-control @error('forma_pago') is-invalid @enderror" required>
                <option value="Mensual" {{ old('forma_pago', $poliza->forma_pago ?? '') == 'Mensual' ? 'selected' : '' }}>Mensual</option>
                <option value="Trimestral" {{ old('forma_pago', $poliza->forma_pago ?? '') == 'Trimestral' ? 'selected' : '' }}>Trimestral</option>
                <option value="Semestral" {{ old('forma_pago', $poliza->forma_pago ?? '') == 'Semestral' ? 'selected' : '' }}>Semestral</option>

            </select>
            @error('forma_pago')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row mt-3">
        <!-- Pago Fraccionado -->
        <div class="col-md-6 form-group">
            <label for="primer_pago_fraccionado">Primer Pago Fraccionado</label>
            <input type="date" name="primer_pago_fraccionado" id="primer_pago_fraccionado" 
                   class="form-control @error('primer_pago_fraccionado') is-invalid @enderror" 
                   value="{{ old('primer_pago_fraccionado', $poliza->primer_pago_fraccionado?->format('Y-m-d') ?? '') }}">
            @error('primer_pago_fraccionado')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- PDF -->
        <div class="col-md-6 form-group">
            <label for="ruta_pdf">Archivo PDF</label>
            <input type="file" name="ruta_pdf" id="ruta_pdf" 
                   class="form-control @error('ruta_pdf') is-invalid @enderror" 
                   accept="application/pdf">
            @error('ruta_pdf')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if($poliza->ruta_pdf ?? false)
                <div class="mt-2">
                    <a href="{{ Storage::url($poliza->ruta_pdf) }}" 
                       class="btn btn-sm btn-outline-primary" 
                       target="_blank">
                        <i class="fas fa-file-pdf"></i> Ver PDF actual
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Botones -->
    <div class="row mt-4">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Actualizar' : 'Guardar' }}
            </button>
            <a href="{{ route('polizas.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </div>
</form>