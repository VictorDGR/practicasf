@extends('layouts.app')

@section('titulo', 'Reporte')

@section('contenido')
<h3 class="mb-3">Reporte</h3>

<form method="GET" action="{{ route('admin.reportes.index') }}" class="row g-2 align-items-end mb-4">

    {{-- Tipo de reporte --}}
    <div class="col-auto">
        <label class="form-label">Tipo de reporte</label>
        <select id="selectTipoReporte" name="tipo" class="form-select">
            <option value="general"       @selected($tipo === 'general')>General</option>
            <option value="por_item"      @selected($tipo === 'por_item')>Por Item</option>
            <option value="arqueo"        @selected($tipo === 'arqueo')>Por arqueo de caja</option>
            <option value="reimpresiones" @selected($tipo === 'reimpresiones')>Reimpresiones</option>
            <option value="devoluciones"  @selected($tipo === 'devoluciones')>Devoluciones</option>
        </select>
    </div>

    {{-- Desde --}}
    <div class="col-auto">
        <label class="form-label">Desde</label>
        <input type="date" name="desde" class="form-control" value="{{ $desde }}">
    </div>

    {{-- Hasta --}}
    <div class="col-auto">
        <label class="form-label">Hasta</label>
        <input type="date" name="hasta" class="form-control" value="{{ $hasta }}">
    </div>

    {{-- Filtro ítem (solo para por_item) --}}
    <div class="col-auto" id="filtroItem"
         style="{{ $tipo === 'por_item' ? '' : 'display:none;' }}">
        <label class="form-label">Item</label>
        <select name="item_id" class="form-select">
            <option value="">Todos los Items</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}" @selected($itemId == $item->id)>{{ $item->nombre }}</option>
            @endforeach
        </select>
    </div>

    {{-- Filtro estado solicitud (solo para reimpresiones y devoluciones) --}}
    <div class="col-auto" id="filtroEstado"
         style="{{ in_array($tipo, ['reimpresiones','devoluciones']) ? '' : 'display:none;' }}">
        <label class="form-label">Estado</label>
        <select name="estado_solicitud" class="form-select">
            <option value="">Todos</option>
            <option value="pendiente"  @selected($estadoSolicitud === 'pendiente')>Pendiente</option>
            <option value="aprobada"   @selected($estadoSolicitud === 'aprobada')>Aprobada</option>
            <option value="rechazada"  @selected($estadoSolicitud === 'rechazada')>Rechazada</option>
        </select>
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <button type="submit" formaction="{{ route('admin.reportes.pdf') }}" class="btn btn-outline-danger">Descargar PDF</button>
        <button type="submit" formaction="{{ route('admin.reportes.excel') }}" class="btn btn-outline-success">Descargar Excel</button>
    </div>
</form>

@include('admin.reportes.resultados')

@endsection

@section('scripts')
<script>
    document.getElementById('selectTipoReporte').addEventListener('change', function () {
        const tipo = this.value;
        document.getElementById('filtroItem').style.display   = tipo === 'por_item' ? '' : 'none';
        document.getElementById('filtroEstado').style.display = ['reimpresiones','devoluciones'].includes(tipo) ? '' : 'none';
    });
</script>
@endsection