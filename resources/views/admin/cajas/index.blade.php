@extends('layouts.app')

@section('titulo', 'Cajas')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Cajas</h3>
    <a href="{{ route('admin.cajas.create') }}" class="btn btn-primary">Nueva caja</a>
</div>
<table class="table table-bordered bg-white">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Situación</th>
            <th>Cajero actual</th>
            <th>Monto en caja</th>
            <th>Estado</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($cajas as $caja)
            @php
                $arqueo = $caja->arqueoAbierto();
                $turno = $arqueo?->turnoAbierto();
            @endphp
            <tr>
                <td>{{ $caja->nombre }}</td>
                <td>
                    @if($arqueo)
                        <span class="badge bg-success">Abierta desde {{ $arqueo->fecha_apertura->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="badge bg-secondary">Cerrada</span>
                    @endif
                </td>
                <td>{{ $turno?->usuario->persona->nombreCompleto() ?? '—' }}</td>
                <td>{{ $arqueo ? 'Bs. ' . number_format($arqueo->montoSistemaCalculado(), 2) : '—' }}</td>
                <td>
                    <span class="badge {{ $caja->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                        {{ $caja->estado }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.cajas.edit', $caja) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">No existe registro</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
