@extends('layouts.app')

@section('titulo', 'Editar estudiante')

@section('contenido')
<h3 class="mb-3">Editar estudiante</h3>
<div class="card bg-white">
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.estudiantes.update', $estudiante) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">CI</label>
                    <input type="text" class="form-control" value="{{ $estudiante->persona_ci }}" disabled>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nombres</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $estudiante->persona->nombre) }}" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Apellido paterno</label>
                    <input type="text" name="ap_paterno" class="form-control" value="{{ old('ap_paterno', $estudiante->persona->ap_paterno) }}" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Apellido materno</label>
                    <input type="text" name="ap_materno" class="form-control" value="{{ old('ap_materno', $estudiante->persona->ap_materno) }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Carrera</label>
                    <select name="carrera_id" class="form-select" required>
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera->id }}" @selected(old('carrera_id', $estudiante->carrera_id) == $carrera->id)>{{ $carrera->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" @selected(old('estado', $estudiante->estado) === 'activo')>activo</option>
                        <option value="inactivo" @selected(old('estado', $estudiante->estado) === 'inactivo')>inactivo</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('superadmin.estudiantes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
