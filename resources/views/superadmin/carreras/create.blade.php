@extends('layouts.app')

@section('titulo', 'Nueva carrera')

@section('contenido')
<h3 class="mb-3">Nueva carrera</h3>

<div class="card bg-white">
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.carreras.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="{{ route('superadmin.carreras.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
