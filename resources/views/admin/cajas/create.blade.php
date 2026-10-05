@extends('layouts.app')

@section('titulo', 'Nueva caja')

@section('contenido')
<h3 class="mb-3">Nueva caja</h3>

<div class="card bg-white">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cajas.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej: Caja 1" required>
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.cajas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
