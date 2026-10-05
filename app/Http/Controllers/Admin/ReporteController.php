<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArqueoCaja;
use App\Models\Cobro;
use App\Models\DetallePago;
use App\Models\Item;
use App\Models\SolicitudReimpresion;
use App\Models\SolicitudDevolucion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Shuchkin\SimpleXLSXGen;

class ReporteController extends Controller
{
    private $nombresTipo = [
        'general' => 'General',
        'por_item' => 'Por ítem',
        'arqueo' => 'Por arqueo de caja',
        'reimpresiones' => 'Reimpresiones',
        'devoluciones' => 'Devoluciones',
    ];

    public function index(Request $request)
    {
        return view('admin.reportes.index', $this->datosReporte($request) + [
            'items' => Item::orderBy('nombre')->get(),
        ]);
    }

    public function pdf(Request $request)
    {
        $datos = $this->datosReporte($request);

        return Pdf::loadView('admin.reportes.pdf', $datos)
            ->setPaper('a4', 'landscape')
            ->download('reporte-' . $datos['tipo'] . '-' . $datos['desde'] . '.pdf');
    }

    public function excel(Request $request)
    {
        $datos = $this->datosReporte($request);
        $xlsx = SimpleXLSXGen::fromArray($this->filasExcel($datos), 'Reporte');

        return response((string) $xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="reporte-' . $datos['tipo'] . '-' . $datos['desde'] . '.xlsx"',
        ]);
    }

    private function datosReporte(Request $request): array
    {
        [$desde, $hasta] = $this->rangoFechas($request);
        $tipo            = array_key_exists($request->input('tipo'), $this->nombresTipo) ? $request->input('tipo') : 'general';
        $itemId          = $request->input('item_id');
        $estadoSolicitud = in_array($request->input('estado_solicitud'), ['pendiente', 'aprobada', 'rechazada'])
                           ? $request->input('estado_solicitud')
                           : null;

        $datos = match ($tipo) {
            'por_item'      => $this->reportePorItem($desde, $hasta, $itemId),
            'arqueo'        => $this->reporteArqueos($desde, $hasta),
            'reimpresiones' => $this->reporteReimpresiones($desde, $hasta, $estadoSolicitud),
            'devoluciones'  => $this->reporteDevoluciones($desde, $hasta, $estadoSolicitud),
            default         => $this->reporteGeneral($desde, $hasta),
        };

        return $datos + [
            'desde'           => $desde->format('Y-m-d'),
            'hasta'           => $hasta->format('Y-m-d'),
            'tipo'            => $tipo,
            'nombreTipo'      => $this->nombresTipo[$tipo],
            'itemId'          => $itemId,
            'estadoSolicitud' => $estadoSolicitud,
        ];
    }

    private function filasExcel(array $d): array
    {
        $filas = [
            ['<b>UPDS - Reporte ' . $d['nombreTipo'] . '</b>'],
            ['Desde', $d['desde'], 'Hasta', $d['hasta']],
            [],
        ];

        if ($d['tipo'] === 'general') {
            $filas[] = ['Cobros registrados', $d['cantidadCobros']];
            $filas[] = ['Total recaudado (Bs.)', (float) $d['totalRecaudado']];
            $filas[] = ['Cobros anulados', $d['cantidadAnulados']];
            $filas[] = [];
            $filas[] = $this->negrita(['Fecha', 'Comprobante', 'CI', 'Estudiante', 'Ítem(s)', 'Cajero', 'Tipo de pago', 'Total (Bs.)', 'Estado']);
            foreach ($d['detalleCobros'] as $cobro) {
                $filas[] = [
                    $cobro->fecha_pago->format('d/m/Y H:i'),
                    $cobro->comprobante?->numero_comprobante ?? '',
                    $cobro->estudiante->persona_ci,
                    $cobro->estudiante->persona->nombreCompleto(),
                    $cobro->detallePagos->pluck('item.nombre')->implode(', '),
                    $cobro->usuario->persona->nombreCompleto(),
                    $cobro->tipo_pago,
                    (float) $cobro->monto_total,
                    $cobro->estado,
                ];
            }
        }

        if ($d['tipo'] === 'por_item') {
            $filas[] = ['Cobros registrados', (int) $d['cantidadCobros']];
            $filas[] = ['Total recaudado (Bs.)', (float) $d['totalRecaudado']];
            $filas[] = [];
            $filas[] = $this->negrita(['Ítem', 'Cantidad vendida', 'Total (Bs.)']);
            foreach ($d['porItem'] as $item) {
                $filas[] = [$item->nombre, (int) $item->cantidad_vendida, (float) $item->total_recaudado];
            }
            $filas[] = [];
            $filas[] = $this->negrita(['Fecha', 'Ítem', 'Estudiante', 'Cajero', 'Cantidad', 'Subtotal (Bs.)']);
            foreach ($d['detalleVentas'] as $detalle) {
                $filas[] = [
                    $detalle->cobro->fecha_pago->format('d/m/Y H:i'),
                    $detalle->item->nombre,
                    $detalle->cobro->estudiante->persona->nombreCompleto(),
                    $detalle->cobro->usuario->persona->nombreCompleto(),
                    $detalle->cantidad,
                    (float) $detalle->subtotal,
                ];
            }
        }

        if ($d['tipo'] === 'arqueo') {
            $filas[] = ['Total recaudado (Bs.)', (float) $d['totalRecaudado']];
            $filas[] = ['Diferencia acumulada (Bs.)', (float) $d['totalDiferencia']];
            $filas[] = [];
            $filas[] = $this->negrita(['Caja', 'Cajeros', 'Apertura', 'Cierre', 'Monto apertura', 'Sistema', 'Físico', 'Diferencia', 'Pagados', 'Anulados', 'Estado']);
            foreach ($d['arqueos'] as $arqueo) {
                $filas[] = [
                    $arqueo->caja?->nombre ?? '',
                    $arqueo->turnos->map(fn ($turno) => $turno->usuario->persona->nombreCompleto())->unique()->implode(', '),
                    $arqueo->fecha_apertura->format('d/m/Y H:i'),
                    $arqueo->fecha_cierre?->format('d/m/Y H:i') ?? '',
                    (float) $arqueo->monto_apertura,
                    $arqueo->monto_cierre_sistema !== null ? (float) $arqueo->monto_cierre_sistema : '',
                    $arqueo->monto_cierre_fisico !== null ? (float) $arqueo->monto_cierre_fisico : '',
                    $arqueo->diferencia !== null ? (float) $arqueo->diferencia : '',
                    $arqueo->cobros_pagados,
                    $arqueo->cobros_anulados,
                    $arqueo->estado,
                ];
            }
        }

        if ($d['tipo'] === 'reimpresiones') {
            $filas[] = ['Total solicitudes', $d['solicitudes']->count()];
            $filas[] = ['Aprobadas', $d['totalAprobadas']];
            $filas[] = ['Rechazadas', $d['totalRechazadas']];
            $filas[] = ['Pendientes', $d['totalPendientes']];
            $filas[] = [];
            $filas[] = $this->negrita(['Fecha solicitud', 'Comprobante', 'Estudiante', 'Ítem(s)', 'Monto (Bs.)', 'Cajero', 'Motivo', 'Estado', 'Fecha autorización', 'Autorizado por']);
            foreach ($d['solicitudes'] as $solicitud) {
                $cobro = $solicitud->comprobante->cobro;
                $filas[] = [
                    $solicitud->fecha_solicitud->format('d/m/Y H:i'),
                    $solicitud->comprobante->numero_comprobante,
                    $cobro->estudiante->persona->nombreCompleto(),
                    $cobro->detallePagos->pluck('item.nombre')->implode(', '),
                    (float) $cobro->monto_total,
                    $solicitud->cajeroSolicitante->persona->nombreCompleto(),
                    $solicitud->motivo,
                    $solicitud->estado,
                    $solicitud->fecha_autorizacion?->format('d/m/Y H:i') ?? '',
                    $solicitud->adminAutoriza?->persona->nombreCompleto() ?? '',
                ];
            }
        }

        if ($d['tipo'] === 'devoluciones') {
            $filas[] = ['Total solicitudes', $d['solicitudes']->count()];
            $filas[] = ['Aprobadas', $d['totalAprobadas']];
            $filas[] = ['Rechazadas', $d['totalRechazadas']];
            $filas[] = ['Monto total devuelto (Bs.)', (float) $d['montoTotalDevuelto']];
            $filas[] = [];
            $filas[] = $this->negrita(['Fecha solicitud', 'Comprobante', 'Estudiante', 'Ítem(s)', 'Monto cobro (Bs.)', 'Monto devuelto (Bs.)', 'Cajero', 'Motivo', 'Estado', 'Fecha resolución', 'Resuelto por']);
            foreach ($d['solicitudes'] as $solicitud) {
                $filas[] = [
                    $solicitud->fecha_solicitud->format('d/m/Y H:i'),
                    $solicitud->cobro->comprobante?->numero_comprobante ?? '',
                    $solicitud->cobro->estudiante->persona->nombreCompleto(),
                    $solicitud->cobro->detallePagos->pluck('item.nombre')->implode(', '),
                    (float) $solicitud->cobro->monto_total,
                    $solicitud->monto_devuelto !== null ? (float) $solicitud->monto_devuelto : '',
                    $solicitud->cajeroSolicitante->persona->nombreCompleto(),
                    $solicitud->motivo,
                    $solicitud->estado,
                    $solicitud->fecha_resolucion?->format('d/m/Y H:i') ?? '',
                    $solicitud->adminAutoriza?->persona->nombreCompleto() ?? '',
                ];
            }
        }

        return $filas;
    }

    private function negrita(array $titulos): array
    {
        return array_map(fn ($titulo) => '<b>' . $titulo . '</b>', $titulos);
    }

    private function reporteGeneral(Carbon $desde, Carbon $hasta): array
    {
        $cobrosPagados = Cobro::whereBetween('fecha_pago', [$desde, $hasta])
            ->where('estado', 'pagado');

        $porTipoPago = (clone $cobrosPagados)
            ->selectRaw('tipo_pago, COUNT(*) as cantidad, SUM(monto_total) as total')
            ->groupBy('tipo_pago')
            ->get();

        $detalleCobros = Cobro::whereBetween('fecha_pago', [$desde, $hasta])
            ->with(['estudiante.persona', 'usuario.persona', 'detallePagos.item', 'comprobante'])
            ->orderByDesc('fecha_pago')
            ->get();

        return [
            'cantidadCobros'   => (clone $cobrosPagados)->count(),
            'cantidadAnulados' => Cobro::whereBetween('fecha_pago', [$desde, $hasta])
                                       ->where('estado', 'anulado')->count(),
            'totalRecaudado'   => (clone $cobrosPagados)->sum('monto_total'),
            'porTipoPago'      => $porTipoPago,
            'detalleCobros'    => $detalleCobros,
        ];
    }

    private function reportePorItem(Carbon $desde, Carbon $hasta, ?string $itemId): array
    {
        $detalle = DetallePago::query()
            ->join('cobros', 'cobros.id', '=', 'detalle_pagos.cobro_id')
            ->join('items', 'items.id', '=', 'detalle_pagos.item_id')
            ->whereBetween('cobros.fecha_pago', [$desde, $hasta])
            ->where('cobros.estado', 'pagado')
            ->when($itemId, fn($q) => $q->where('detalle_pagos.item_id', $itemId));

        $porItem = (clone $detalle)
            ->selectRaw('items.id, items.nombre, SUM(detalle_pagos.cantidad) as cantidad_vendida, SUM(detalle_pagos.subtotal) as total_recaudado')
            ->groupBy('items.id', 'items.nombre')
            ->orderByDesc('total_recaudado')
            ->get();

        $detalleVentas = DetallePago::with(['item', 'cobro.estudiante.persona', 'cobro.usuario.persona'])
            ->whereHas('cobro', function($q) use ($desde, $hasta) {
                $q->whereBetween('fecha_pago', [$desde, $hasta])->where('estado', 'pagado');
            })
            ->when($itemId, fn($q) => $q->where('item_id', $itemId))
            ->get()
            ->sortByDesc(fn($d) => $d->cobro->fecha_pago);

        return [
            'porItem'        => $porItem,
            'cantidadCobros' => (clone $detalle)->selectRaw('COUNT(DISTINCT cobros.id) as total')->value('total'),
            'totalRecaudado' => $porItem->sum('total_recaudado'),
            'detalleVentas'  => $detalleVentas,
        ];
    }

    private function reporteArqueos(Carbon $desde, Carbon $hasta): array
    {
        $arqueos = ArqueoCaja::with(['caja', 'usuario.persona', 'turnos.usuario.persona'])
            ->withCount([
                'cobros as cobros_pagados'  => fn($q) => $q->where('estado', 'pagado'),
                'cobros as cobros_anulados' => fn($q) => $q->where('estado', 'anulado'),
            ])
            ->whereBetween('fecha_apertura', [$desde, $hasta])
            ->orderByDesc('fecha_apertura')
            ->get();

        $totalRecaudado = Cobro::whereIn('arqueo_caja_id', $arqueos->pluck('id'))
            ->where('estado', 'pagado')
            ->sum('monto_total');

        return [
            'arqueos'         => $arqueos,
            'totalRecaudado'  => $totalRecaudado,
            'totalDiferencia' => $arqueos->sum('diferencia'),
        ];
    }

    private function reporteReimpresiones(Carbon $desde, Carbon $hasta, ?string $estado): array
    {
        $solicitudes = SolicitudReimpresion::with([
                'comprobante.cobro.estudiante.persona',
                'comprobante.cobro.detallePagos.item',
                'cajeroSolicitante.persona',
                'adminAutoriza.persona',
            ])
            ->whereBetween('fecha_solicitud', [$desde, $hasta])
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->orderByDesc('fecha_solicitud')
            ->get();

        return [
            'solicitudes'     => $solicitudes,
            'totalPendientes' => $solicitudes->where('estado', 'pendiente')->count(),
            'totalAprobadas'  => $solicitudes->where('estado', 'aprobada')->count(),
            'totalRechazadas' => $solicitudes->where('estado', 'rechazada')->count(),
        ];
    }

    private function reporteDevoluciones(Carbon $desde, Carbon $hasta, ?string $estado): array
    {
        $solicitudes = SolicitudDevolucion::with([
                'cobro.estudiante.persona',
                'cobro.detallePagos.item',
                'cobro.comprobante',
                'cobro.arqueoCaja',
                'cajeroSolicitante.persona',
                'adminAutoriza.persona',
            ])
            ->whereBetween('fecha_solicitud', [$desde, $hasta])
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->orderByDesc('fecha_solicitud')
            ->get();

        return [
            'solicitudes'        => $solicitudes,
            'totalPendientes'    => $solicitudes->where('estado', 'pendiente')->count(),
            'totalAprobadas'     => $solicitudes->where('estado', 'aprobada')->count(),
            'totalRechazadas'    => $solicitudes->where('estado', 'rechazada')->count(),
            'montoTotalDevuelto' => $solicitudes->where('estado', 'aprobada')->sum('monto_devuelto'),
        ];
    }

    private function rangoFechas(Request $request): array
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : now()->startOfDay();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : now()->endOfDay();

        if ($hasta < $desde) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [$desde, $hasta];
    }
}