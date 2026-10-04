<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Models\Cobro;
use App\Models\DetallePago;
use App\Models\Estudiante;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CobroController extends Controller
{
    public function create(Request $request)
    {
        $items = Item::activos()
            ->with(['adicionales' => fn ($q) => $q->where('estado', 'activo')->orderBy('nombre')])
            ->orderBy('nombre')
            ->get();

        $itemsJson = $items->map(fn ($item) => [
            'id' => $item->id,
            'nombre' => $item->nombre,
            'monto' => (float) $item->monto,
            'adicionales' => $item->adicionales->map(fn ($adicional) => [
                'id' => $adicional->id,
                'nombre' => $adicional->nombre,
                'monto' => (float) $adicional->monto,
            ]),
        ]);

        $estudiantes = Estudiante::with(['persona', 'carrera'])->where('estado', 'activo')->get();
        $arqueo = $request->user()->arqueoAbierto();

        $estudiantesJson = $estudiantes->map(function ($estudiante) {
            $apellidos = trim("{$estudiante->persona->ap_paterno} {$estudiante->persona->ap_materno}");

            return [
                'id' => $estudiante->id,
                'ci' => $estudiante->persona_ci,
                'nombre' => $estudiante->persona->nombre,
                'apellidos' => $apellidos,
                'carrera' => $estudiante->carrera->nombre,
                'etiqueta' => "{$estudiante->persona->nombre} {$apellidos} — CI {$estudiante->persona_ci}",
            ];
        });

        return view('cajero.cobros.create', compact('itemsJson', 'estudiantesJson', 'arqueo'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'estudiante_id' => ['required', Rule::exists('estudiantes', 'id')->where('estado', 'activo')],
            'tipo_pago' => ['required', 'in:efectivo,tarjeta,transferencia'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'distinct', Rule::exists('items', 'id')->where('estado', 'activo')],
            'items.*.adicionales' => ['nullable', 'array'],
            'items.*.adicionales.*' => ['integer'],
        ]);

        $arqueo = $request->user()->arqueoAbierto();

        if (! $arqueo) {
            return redirect()->route('cajero.caja.apertura');
        }

        $lineas = [];
        $total = 0;

        foreach ($datos['items'] as $fila) {
            $item = Item::findOrFail($fila['item_id']);
            $idsAdicionales = array_unique($fila['adicionales'] ?? []);

            $adicionales = $item->adicionales()
                ->where('estado', 'activo')
                ->whereIn('id', $idsAdicionales)
                ->get();

            if ($adicionales->count() !== count($idsAdicionales)) {
                throw ValidationException::withMessages([
                    'items' => "Uno de los adicionales elegidos para {$item->nombre} no es válido.",
                ]);
            }

            $subtotal = (float) $item->monto + (float) $adicionales->sum('monto');
            $total += $subtotal;
            $lineas[] = compact('item', 'adicionales', 'subtotal');
        }

        $cobro = DB::transaction(function () use ($datos, $request, $arqueo, $lineas, $total) {
            $cobro = Cobro::create([
                'usuario_id' => $request->user()->id,
                'estudiante_id' => $datos['estudiante_id'],
                'arqueo_caja_id' => $arqueo->id,
                'monto_total' => $total,
                'tipo_pago' => $datos['tipo_pago'],
                'fecha_pago' => now(),
                'estado' => 'pagado',
            ]);

            foreach ($lineas as $linea) {
                $detalle = DetallePago::create([
                    'cobro_id' => $cobro->id,
                    'item_id' => $linea['item']->id,
                    'cantidad' => 1,
                    'subtotal' => $linea['subtotal'],
                ]);

                foreach ($linea['adicionales'] as $adicional) {
                    $detalle->adicionales()->create([
                        'item_adicional_id' => $adicional->id,
                        'monto' => $adicional->monto,
                    ]);
                }
            }

            Comprobante::create([
                'cobro_id' => $cobro->id,
                'numero_comprobante' => 'C-' . str_pad($cobro->id, 8, '0', STR_PAD_LEFT),
                'fecha_emision' => now(),
                'es_reimpresion' => false,
                'anulado' => false,
            ]);

            return $cobro;
        });

        return redirect()->route('cajero.cobros.comprobante', $cobro);
    }

    public function comprobante(Request $request, Cobro $cobro)
    {
        if ($cobro->usuario_id !== $request->user()->id) {
            abort(403);
        }

        $cobro->load(['estudiante.persona', 'usuario.persona', 'detallePagos.item', 'detallePagos.adicionales.itemAdicional', 'comprobante']);

        return view('cajero.cobros.comprobante', compact('cobro'));
    }
}
