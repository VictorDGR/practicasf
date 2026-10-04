<div class="comprobante-fila comprobante-encabezado"><span>Detalle</span><span>Monto</span></div>
@foreach($cobro->detallePagos as $detalle)
    <div class="comprobante-fila">
        <span>{{ $detalle->item->nombre }}</span>
        <span>{{ number_format($detalle->montoItem(), 2) }}</span>
    </div>
    @foreach($detalle->adicionales as $adicional)
        <div class="comprobante-fila comprobante-adicional">
            <span>+ {{ $adicional->itemAdicional->nombre }}</span>
            <span>{{ number_format($adicional->monto, 2) }}</span>
        </div>
    @endforeach
@endforeach
<hr>
<div class="comprobante-fila comprobante-total">
    <span>TOTAL</span>
    <span>{{ number_format($cobro->monto_total, 2) }}</span>
</div>
