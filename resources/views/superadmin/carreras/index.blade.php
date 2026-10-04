@extends('layouts.app')

@section('titulo', 'Carreras')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Carreras</h3>
    <a href="{{ route('superadmin.carreras.create') }}" class="btn btn-primary">Nueva carrera</a>
</div>
<table class="table table-bordered bg-white">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Estudiantes</th>
            <th>Estado</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($carreras as $carrera)
            <tr>
                <td>{{ $carrera->nombre }}</td>
                <td>{{ $carrera->estudiantes_count }}</td>
                <td>
                    <span class="badge {{ $carrera->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                        {{ $carrera->estado }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('superadmin.carreras.edit', $carrera) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">No existe registro</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
