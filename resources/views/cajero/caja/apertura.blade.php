@extends('layouts.app')

@section('titulo', 'Iniciar turno')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card bg-white mt-4">
            <div class="card-body">
                <h4 class="card-title mb-1">Iniciar turno</h4>
                <p class="text-muted mb-4">Selecciona la caja en la que vas a trabajar</p>

                @if($cajasJson->isEmpty())
                    <div class="alert alert-warning mb-0">No hay cajas registradas. Pide a un administrador que cree una caja.</div>
                @else
                    <form method="POST" action="{{ route('cajero.caja.abrir') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Caja</label>
                            <select name="caja_id" id="selectCaja" class="form-select" required>
                                <option value="">Seleccione una caja</option>
                                @foreach($cajasJson as $caja)
                                    <option value="{{ $caja['id'] }}" @selected(old('caja_id') == $caja['id']) @disabled($caja['ocupada_por'])>
                                        {{ $caja['nombre'] }}
                                        @if($caja['ocupada_por'])
                                            (en uso por {{ $caja['ocupada_por'] }})
                                        @elseif($caja['abierta'])
                                            (abierta, continuar turno)
                                        @else
                                            (cerrada)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="bloqueApertura" class="d-none">
                            <div class="alert alert-info">
                                La caja está cerrada. Vas a <strong>aperturar la caja</strong> del día, la caja inicia en Bs. 0.00 y debes colocar el fondo de caja (sencillo para cambio).
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fondo de caja para cambio (Bs)</label>
                                <input type="number" step="0.01" min="0" name="monto_apertura" id="inputFondo" class="form-control" value="{{ old('monto_apertura') }}">
                            </div>
                        </div>

                        <div id="bloqueContinuar" class="d-none">
                            <div class="alert alert-info">
                                La caja ya está abierta. Recibes la caja del cajero anterior con:
                                <div class="fs-4 fw-semibold mt-1">Bs. <span id="montoActual">0.00</span></div>
                                <small>Verifica que el dinero que recibes sea este monto.</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Iniciar turno</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const cajas = @json($cajasJson);
    const selectCaja = document.getElementById('selectCaja');

    function mostrarDatosCaja() {
        const caja = cajas.find(c => c.id == selectCaja.value);
        const bloqueApertura = document.getElementById('bloqueApertura');
        const bloqueContinuar = document.getElementById('bloqueContinuar');
        const inputFondo = document.getElementById('inputFondo');

        bloqueApertura.classList.add('d-none');
        bloqueContinuar.classList.add('d-none');
        inputFondo.required = false;

        if (!caja) {
            return;
        }

        if (caja.abierta) {
            document.getElementById('montoActual').textContent = Number(caja.monto_actual).toFixed(2);
            bloqueContinuar.classList.remove('d-none');
        } else {
            inputFondo.required = true;
            bloqueApertura.classList.remove('d-none');
        }
    }

    if (selectCaja) {
        selectCaja.addEventListener('change', mostrarDatosCaja);
        mostrarDatosCaja();
    }
</script>
@endsection
