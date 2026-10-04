@extends('layouts.app')

@section('titulo', 'Estudiantes')

@section('contenido')
<h3 class="mb-3">Estudiantes</h3>

@if(session('erroresImportacion'))
    <div class="alert alert-warning">
        <strong>Filas que no se importaron:</strong>
        <ul class="mb-0">
            @foreach(session('erroresImportacion') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card bg-white mb-4">
    <div class="card-body">
        <h5 class="card-title">Importar desde Excel</h5>
        <p class="text-muted small mb-2">
            El archivo debe ser <strong>.xlsx</strong> y la primera fila debe tener estas columnas:
            <code>ci</code>, <code>nombre1</code>, <code>nombre2</code>, <code>apellidop</code>, <code>apellidom</code>, <code>carrera</code>.
            Opcionalmente puede tener <code>complemento</code> (ej: CI 6565204 con complemento 1B queda como 6565204-1B).
            Si la carrera no existe se crea sola. Si el CI ya existe se actualizan sus datos, no se duplica.
        </p>
        <form method="POST" action="{{ route('superadmin.estudiantes.importar') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-8">
                <input type="file" name="archivo" accept=".xlsx" class="form-control" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100">Importar</button>
            </div>
        </form>
    </div>
</div>

<form method="GET" action="{{ route('superadmin.estudiantes.index') }}" class="row g-2 mb-3">
    <div class="col-md-8">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar por CI, nombre o apellido" value="{{ $buscar }}">
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-outline-primary w-100">Buscar</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>CI</th>
                <th>Nombre</th>
                <th>Carrera</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($estudiantes as $estudiante)
                <tr>
                    <td>{{ $estudiante->persona_ci }}</td>
                    <td>{{ $estudiante->persona->nombreCompleto() }}</td>
                    <td>{{ $estudiante->carrera->nombre }}</td>
                    <td>
                        <span class="badge {{ $estudiante->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $estudiante->estado }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('superadmin.estudiantes.edit', $estudiante) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No existe registro</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $estudiantes->links() }}
@endsection
