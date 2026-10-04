@php $filas = old('adicionales', $filas ?? []); @endphp
<div class="card bg-light mb-3">
    <div class="card-body">
        <h6 class="mb-1">Adicionales (opcional)</h6>
        <p class="text-muted small">Cosas que el estudiante puede elegir llevar junto al ítem, por ejemplo materiales. Su precio se suma al del ítem.</p>

        <div id="listaAdicionales">
            @foreach($filas as $i => $fila)
                <div class="row g-2 mb-2">
                    <input type="hidden" name="adicionales[{{ $i }}][id]" value="{{ $fila['id'] ?? '' }}">
                    <div class="col-6">
                        <input type="text" name="adicionales[{{ $i }}][nombre]" class="form-control" placeholder="Nombre" value="{{ $fila['nombre'] ?? '' }}" required>
                    </div>
                    <div class="col-3">
                        <input type="number" step="0.01" min="0.01" name="adicionales[{{ $i }}][monto]" class="form-control" placeholder="Monto (Bs.)" value="{{ $fila['monto'] ?? '' }}" required>
                    </div>
                    <div class="col-3">
                        @if(! empty($fila['id']))
                            <select name="adicionales[{{ $i }}][estado]" class="form-select">
                                <option value="activo" @selected(($fila['estado'] ?? 'activo') === 'activo')>activo</option>
                                <option value="inactivo" @selected(($fila['estado'] ?? '') === 'inactivo')>inactivo</option>
                            </select>
                        @else
                            <button type="button" class="btn btn-outline-danger w-100 btn-quitar-adicional">Quitar</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" id="btnAgregarAdicional" class="btn btn-sm btn-outline-primary">+ Agregar adicional</button>
    </div>
</div>

<template id="plantillaAdicional">
    <div class="row g-2 mb-2">
        <input type="hidden" name="adicionales[__i__][id]" value="">
        <div class="col-6">
            <input type="text" name="adicionales[__i__][nombre]" class="form-control" placeholder="Nombre" required>
        </div>
        <div class="col-3">
            <input type="number" step="0.01" min="0.01" name="adicionales[__i__][monto]" class="form-control" placeholder="Monto (Bs.)" required>
        </div>
        <div class="col-3">
            <button type="button" class="btn btn-outline-danger w-100 btn-quitar-adicional">Quitar</button>
        </div>
    </div>
</template>
