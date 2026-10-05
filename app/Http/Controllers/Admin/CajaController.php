<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $cajas = Caja::orderBy('nombre')->get();

        return view('admin.cajas.index', compact('cajas'));
    }

    public function create()
    {
        return view('admin.cajas.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:cajas,nombre'],
        ]);

        Caja::create([
            'nombre' => $datos['nombre'],
            'estado' => 'activo',
        ]);

        return redirect()->route('admin.cajas.index')->with('mensaje', 'Caja creada correctamente.');
    }

    public function edit(Caja $caja)
    {
        return view('admin.cajas.edit', compact('caja'));
    }

    public function update(Request $request, Caja $caja)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:cajas,nombre,' . $caja->id],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        if ($datos['estado'] === 'inactivo' && $caja->arqueoAbierto()) {
            return back()->withErrors(['estado' => 'No puedes desactivar una caja que está abierta. Primero se debe cerrar la caja.'])->withInput();
        }

        $caja->update($datos);

        return redirect()->route('admin.cajas.index')->with('mensaje', 'Caja actualizada correctamente.');
    }
}
