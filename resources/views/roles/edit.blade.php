{{-- resources/views/roles/edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Editar Rol')

@section('content_header')
    <h1>Editar Rol</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre del Rol</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="permissions" class="form-label">Selecciona los Permisos</label>
                    <div class="form-check">
                        @foreach($permissions as $permission)
                            <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $permission->id }}"
                                @if($role->permissions->contains($permission->id)) checked @endif>
                            <label class="form-check-label">{{ $permission->name }}</label><br>
                        @endforeach
                    </div>
                    @error('permissions')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Rol</button>
            </form>
        </div>
    </div>
@stop
