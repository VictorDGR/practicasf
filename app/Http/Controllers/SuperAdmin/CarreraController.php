<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use Illuminate\Http\Request;

class CarreraController extends Controller
{
    public function index()
    {
        $carreras = Carrera::withCount('estudiantes')->orderBy('nombre')->get();

        return view('superadmin.carreras.index', compact('carreras'));
    }

    public function create()
    {
        return view('superadmin.carreras.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:carreras,nombre'],
        ]);

        Carrera::create([
            'nombre' => $datos['nombre'],
            'estado' => 'activo',
        ]);

        return redirect()->route('superadmin.carreras.index')->with('mensaje', 'Carrera creada correctamente.');
    }

    public function edit(Carrera $carrera)
    {
        return view('superadmin.carreras.edit', compact('carrera'));
    }

    public function update(Request $request, Carrera $carrera)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:carreras,nombre,' . $carrera->id],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $carrera->update($datos);

        return redirect()->route('superadmin.carreras.index')->with('mensaje', 'Carrera actualizada correctamente.');
    }
}
