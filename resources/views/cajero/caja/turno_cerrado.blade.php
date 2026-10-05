@extends('layouts.app')

@section('titulo', 'Turno cerrado')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card bg-white mt-4">
            <div class="card-body">
                <h4 class="card-title mb-3">Turno cerrado — {{ $turno->arqueoCaja->caja->nombre }}</h4>
                <table class="table table-sm">
                    <tr>
                        <td>Inicio del turno</td>
                        <td class="text-end">{{ $turno->fecha_inicio->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Fin del turno</td>
                        <td class="text-end">{{ $turno->fecha_fin->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Recibiste la caja con</td>
                        <td class="text-end">Bs. {{ number_format($turno->monto_inicial, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Cobrado en tu turno</td>
                        <td class="text-end">Bs. {{ number_format($turno->totalRecaudado(), 2) }}</td>
                    </tr>
                    <tr class="fw-bold">
                        <td>Dejas la caja con</td>
                        <td class="text-end">Bs. {{ number_format($turno->monto_final, 2) }}</td>
                    </tr>
                </table>

                <div class="alert alert-info">Deja el dinero en la caja, el siguiente cajero continuará con este monto.</div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
