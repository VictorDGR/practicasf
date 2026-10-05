@extends('layouts.app')

@section('titulo', 'Backups')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Backups de la base de datos</h3>
    <form method="POST" action="{{ route('superadmin.backups.crear') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Crear nuevo backup</button>
    </form>
</div>
<table class="table table-bordered bg-white">
    <thead>
        <tr>
            <th>Archivo</th>
            <th>Fecha</th>
            <th>Tamaño</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($backups as $backup)
            <tr>
                <td>{{ $backup['nombre'] }}</td>
                <td>{{ $backup['fecha'] }}</td>
                <td>{{ $backup['tamano'] }}</td>
                <td>
                    <a href="{{ route('superadmin.backups.descargar', $backup['nombre']) }}" class="btn btn-sm btn-outline-primary">Descargar</a>
                    <form method="POST" action="{{ route('superadmin.backups.eliminar', $backup['nombre']) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este backup?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
@endsection
