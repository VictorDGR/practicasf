@extends('layouts.app')

@section('titulo', 'Cerrar turno o caja')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-10">
        <h3 class="mb-3">{{ $arqueo->caja->nombre }} — Cerrar turno o caja</h3>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card bg-white h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="mt-0">Cerrar turno</h5>
                        <p class="text-muted">Úsalo cuando te vas y otro cajero continúa en esta caja. No se hace arqueo, el dinero queda en la caja para el siguiente cajero.</p>

                        <table class="table table-sm">
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
                                <td class="text-end">Bs. {{ number_format($montoSistema, 2) }}</td>
                            </tr>
                        </table>

                        <form method="POST" action="{{ route('cajero.caja.cerrar_turno') }}" class="mt-auto" onsubmit="return confirm('¿Cerrar tu turno? El siguiente cajero continuará con Bs. {{ number_format($montoSistema, 2) }}')">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">Cerrar turno</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card bg-white h-100">
                    <div class="card-body">
                        <h5 class="mt-0">Cerrar caja (arqueo del día)</h5>
                        <p class="text-muted">Úsalo solo si eres el último turno del día. Debes contar todo el dinero de la caja.</p>

                        <table class="table table-sm">
                            <tr>
                                <td>Fondo de apertura</td>
                                <td class="text-end">Bs. {{ number_format($arqueo->monto_apertura, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Recaudado en el día (todos los turnos)</td>
                                <td class="text-end">Bs. {{ number_format($arqueo->totalRecaudado(), 2) }}</td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Total que debería haber en caja</td>
                                <td class="text-end">Bs. {{ number_format($montoSistema, 2) }}</td>
                            </tr>
                        </table>

                        <form method="POST" action="{{ route('cajero.caja.confirmar_cierre') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Monto contado físicamente (Bs)</label>
                                <input type="number" step="0.01" min="0" name="monto_cierre_fisico" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-danger w-100">Confirmar cierre de caja</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
