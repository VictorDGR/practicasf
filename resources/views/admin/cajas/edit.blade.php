@extends('layouts.app')

@section('titulo', 'Editar caja')

@section('contenido')
<h3 class="mb-3">Editar caja</h3>
<div class="card bg-white">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cajas.update', $caja) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $caja->nombre) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" @selected(old('estado', $caja->estado) === 'activo')>activo</option>
                        <option value="inactivo" @selected(old('estado', $caja->estado) === 'inactivo')>inactivo</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('admin.cajas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
