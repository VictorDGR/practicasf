<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use App\Models\ArqueoCaja;
use App\Models\Caja;
use App\Models\TurnoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CajaController extends Controller
{
    public function apertura(Request $request)
    {
        if ($request->user()->turnoAbierto()) {
            return redirect()->route('cajero.cobros.create');
        }

        $cajas = Caja::where('estado', 'activo')->orderBy('nombre')->get();

        $cajasJson = $cajas->map(function ($caja) {
            $arqueo = $caja->arqueoAbierto();
            $turno = $arqueo?->turnoAbierto();

            return [
                'id' => $caja->id,
                'nombre' => $caja->nombre,
                'abierta' => (bool) $arqueo,
                'ocupada_por' => $turno?->usuario->persona->nombreCompleto(),
                'monto_actual' => $arqueo ? $arqueo->montoSistemaCalculado() : 0,
            ];
        });

        return view('cajero.caja.apertura', compact('cajasJson'));
    }

    public function abrir(Request $request)
    {
        if ($request->user()->turnoAbierto()) {
            return redirect()->route('cajero.cobros.create');
        }

        $datos = $request->validate([
            'caja_id' => ['required', Rule::exists('cajas', 'id')->where('estado', 'activo')],
            'monto_apertura' => ['nullable', 'numeric', 'min:0'],
        ]);

        $resultado = DB::transaction(function () use ($datos, $request) {
            $caja = Caja::lockForUpdate()->find($datos['caja_id']);
            $arqueo = $caja->arqueoAbierto();
            $turnoActual = $arqueo?->turnoAbierto();

            if ($turnoActual) {
                return 'La ' . $caja->nombre . ' está siendo usada por ' . $turnoActual->usuario->persona->nombreCompleto() . '. Debe cerrar su turno primero.';
            }

            if (! $arqueo) {
                if (! isset($datos['monto_apertura'])) {
                    return 'Ingresa el fondo de caja con el que aperturas.';
                }

                $arqueo = ArqueoCaja::create([
                    'caja_id' => $caja->id,
                    'usuario_id' => $request->user()->id,
                    'monto_apertura' => $datos['monto_apertura'],
                    'fecha_apertura' => now(),
                    'estado' => 'abierto',
                ]);
            }

            TurnoCaja::create([
                'arqueo_caja_id' => $arqueo->id,
                'usuario_id' => $request->user()->id,
                'monto_inicial' => $arqueo->montoSistemaCalculado(),
                'fecha_inicio' => now(),
                'estado' => 'abierto',
            ]);

            return null;
        });

        if ($resultado) {
            return back()->withErrors(['caja_id' => $resultado])->withInput();
        }

        return redirect()->route('cajero.cobros.create')->with('mensaje', 'Turno iniciado correctamente.');
    }

    public function cerrar(Request $request)
    {
        $turno = $request->user()->turnoAbierto();

        if (! $turno) {
            return redirect()->route('cajero.caja.apertura');
        }

        $arqueo = $turno->arqueoCaja;
        $montoSistema = $arqueo->montoSistemaCalculado();

        return view('cajero.caja.cerrar', compact('turno', 'arqueo', 'montoSistema'));
    }

    public function cerrarTurno(Request $request)
    {
        $turno = $request->user()->turnoAbierto();

        if (! $turno) {
            return redirect()->route('cajero.caja.apertura');
        }

        $turno->update([
            'monto_final' => $turno->arqueoCaja->montoSistemaCalculado(),
            'fecha_fin' => now(),
            'estado' => 'cerrado',
        ]);

        return view('cajero.caja.turno_cerrado', compact('turno'));
    }

    public function confirmarCierre(Request $request)
    {
        $turno = $request->user()->turnoAbierto();

        if (! $turno) {
            return redirect()->route('cajero.caja.apertura');
        }

        $arqueo = $turno->arqueoCaja;

        $datos = $request->validate([
            'monto_cierre_fisico' => ['required', 'numeric', 'min:0'],
        ]);

        $montoSistema = $arqueo->montoSistemaCalculado();
        $diferencia = round($datos['monto_cierre_fisico'] - $montoSistema, 2);

        if ($diferencia !== 0.0) {
            $mensaje = $diferencia > 0
                ? 'Hay un sobrante de Bs. ' . number_format($diferencia, 2) . '. La caja debe cuadrar exactamente para poder cerrarla, vuelve a contar el dinero.'
                : 'Hay un faltante de Bs. ' . number_format(abs($diferencia), 2) . '. La caja debe cuadrar exactamente para poder cerrarla, vuelve a contar el dinero.';

            return back()->withErrors(['monto_cierre_fisico' => $mensaje]);
        }

        DB::transaction(function () use ($turno, $arqueo, $montoSistema, $datos, $diferencia) {
            $turno->update([
                'monto_final' => $montoSistema,
                'fecha_fin' => now(),
                'estado' => 'cerrado',
            ]);

            $arqueo->update([
                'monto_cierre_sistema' => $montoSistema,
                'monto_cierre_fisico' => $datos['monto_cierre_fisico'],
                'diferencia' => $diferencia,
                'estado' => 'cerrado',
                'fecha_cierre' => now(),
            ]);
        });

        return view('cajero.caja.resultado', compact('arqueo'));
    }
}
