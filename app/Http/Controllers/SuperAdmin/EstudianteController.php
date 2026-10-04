<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shuchkin\SimpleXLSX;

class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('buscar'));

        $estudiantes = Estudiante::with(['persona', 'carrera'])
            ->when($buscar, function ($query) use ($buscar) {
                $query->whereHas('persona', function ($q) use ($buscar) {
                    $q->where('ci', 'like', "%{$buscar}%")
                        ->orWhere('nombre', 'like', "%{$buscar}%")
                        ->orWhere('ap_paterno', 'like', "%{$buscar}%")
                        ->orWhere('ap_materno', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('superadmin.estudiantes.index', compact('estudiantes', 'buscar'));
    }

    public function edit(Estudiante $estudiante)
    {
        $estudiante->load('persona');
        $carreras = Carrera::orderBy('nombre')->get();

        return view('superadmin.estudiantes.edit', compact('estudiante', 'carreras'));
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'ap_paterno' => ['required', 'string', 'max:100'],
            'ap_materno' => ['nullable', 'string', 'max:100'],
            'carrera_id' => ['required', 'exists:carreras,id'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        DB::transaction(function () use ($datos, $estudiante) {
            $estudiante->persona->update([
                'nombre' => $datos['nombre'],
                'ap_paterno' => $datos['ap_paterno'],
                'ap_materno' => $datos['ap_materno'] ?? null,
            ]);

            $estudiante->update([
                'carrera_id' => $datos['carrera_id'],
                'estado' => $datos['estado'],
            ]);
        });

        return redirect()->route('superadmin.estudiantes.index')->with('mensaje', 'Estudiante actualizado correctamente.');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'extensions:xlsx', 'max:5120'],
        ]);

        $xlsx = SimpleXLSX::parse($request->file('archivo')->getRealPath());

        if (! $xlsx) {
            return back()->withErrors(['archivo' => 'No se pudo leer el archivo Excel.']);
        }

        $filas = $xlsx->rows();
        $encabezados = array_map(fn ($valor) => strtolower(trim((string) $valor)), array_shift($filas) ?? []);
        $faltantes = array_diff(['ci', 'nombre1', 'nombre2', 'apellidop', 'apellidom', 'carrera'], $encabezados);

        if ($faltantes) {
            return back()->withErrors(['archivo' => 'Al Excel le faltan las columnas: ' . implode(', ', $faltantes) . '.']);
        }

        $posicion = array_flip($encabezados);
        $columnas = ['ci', 'complemento', 'nombre1', 'nombre2', 'apellidop', 'apellidom', 'carrera'];
        $creados = 0;
        $actualizados = 0;
        $errores = [];

        DB::transaction(function () use ($filas, $columnas, $posicion, &$creados, &$actualizados, &$errores) {
            foreach ($filas as $indice => $fila) {
                $numeroFila = $indice + 2;
                $datos = [];

                foreach ($columnas as $columna) {
                    $valor = isset($posicion[$columna]) ? ($fila[$posicion[$columna]] ?? '') : '';
                    $datos[$columna] = trim((string) $valor);
                }

                if (implode('', $datos) === '') {
                    continue;
                }

                $vacios = [];
                foreach (['ci', 'nombre1', 'apellidop', 'carrera'] as $obligatorio) {
                    if ($datos[$obligatorio] === '') {
                        $vacios[] = $obligatorio;
                    }
                }

                if ($vacios) {
                    $errores[] = "Fila {$numeroFila}: falta " . implode(', ', $vacios) . '.';
                    continue;
                }

                $ci = $this->armarCi($datos['ci'], $datos['complemento']);

                $carrera = Carrera::firstOrCreate(
                    ['nombre' => $datos['carrera']],
                    ['estado' => 'activo']
                );

                Persona::updateOrCreate(
                    ['ci' => $ci],
                    [
                        'nombre' => trim($datos['nombre1'] . ' ' . $datos['nombre2']),
                        'ap_paterno' => $datos['apellidop'],
                        'ap_materno' => $datos['apellidom'] ?: null,
                    ]
                );

                $estudiante = Estudiante::where('persona_ci', $ci)->first();

                if ($estudiante) {
                    $estudiante->update(['carrera_id' => $carrera->id]);
                    $actualizados++;
                } else {
                    Estudiante::create([
                        'persona_ci' => $ci,
                        'carrera_id' => $carrera->id,
                        'estado' => 'activo',
                    ]);
                    $creados++;
                }
            }
        });

        return redirect()
            ->route('superadmin.estudiantes.index')
            ->with('mensaje', "Importación terminada: {$creados} estudiante(s) nuevo(s), {$actualizados} actualizado(s).")
            ->with('erroresImportacion', $errores);
    }

    private function armarCi(string $ci, string $complemento): string
    {
        $ci = strtoupper(str_replace(' ', '', $ci));
        $complemento = strtoupper(str_replace([' ', '-'], '', $complemento));

        if ($complemento !== '' && ! str_contains($ci, '-')) {
            $ci .= '-' . $complemento;
        }

        return $ci;
    }
}
