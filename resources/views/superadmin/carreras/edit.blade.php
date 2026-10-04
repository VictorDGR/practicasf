@extends('layouts.app')

@section('titulo', 'Editar carrera')

@section('contenido')
<h3 class="mb-3">Editar carrera</h3>
<div class="card bg-white">
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.carreras.update', $carrera) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $carrera->nombre) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" @selected(old('estado', $carrera->estado) === 'activo')>activo</option>
                        <option value="inactivo" @selected(old('estado', $carrera->estado) === 'inactivo')>inactivo</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('superadmin.carreras.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
