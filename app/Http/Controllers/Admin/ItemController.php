<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['creador.persona', 'adicionales'])->orderByDesc('id')->get();
        return view('admin.items.index', compact('items'));
    }
    public function create()
    {
        return view('admin.items.create');
    }
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'adicionales' => ['nullable', 'array'],
            'adicionales.*.nombre' => ['required', 'string', 'max:100'],
            'adicionales.*.monto' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () use ($datos, $request) {
            $item = Item::create([
                'creado_por' => $request->user()->id,
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'monto' => $datos['monto'],
                'estado' => 'activo',
            ]);

            foreach ($datos['adicionales'] ?? [] as $adicional) {
                $item->adicionales()->create([
                    'nombre' => $adicional['nombre'],
                    'monto' => $adicional['monto'],
                    'estado' => 'activo',
                ]);
            }
        });

        return redirect()->route('admin.items.index')->with('mensaje', 'Ítem creado correctamente.');
    }
    public function edit(Item $item)
    {
        $item->load('adicionales');
        return view('admin.items.edit', compact('item'));
    }
    public function update(Request $request, Item $item)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'estado' => ['required', 'in:activo,inactivo'],
            'adicionales' => ['nullable', 'array'],
            'adicionales.*.id' => ['nullable', 'integer'],
            'adicionales.*.nombre' => ['required', 'string', 'max:100'],
            'adicionales.*.monto' => ['required', 'numeric', 'min:0.01'],
            'adicionales.*.estado' => ['nullable', 'in:activo,inactivo'],
        ]);

        DB::transaction(function () use ($datos, $item) {
            $item->update([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'monto' => $datos['monto'],
                'estado' => $datos['estado'],
            ]);

            foreach ($datos['adicionales'] ?? [] as $adicional) {
                if (! empty($adicional['id'])) {
                    $item->adicionales()->where('id', $adicional['id'])->update([
                        'nombre' => $adicional['nombre'],
                        'monto' => $adicional['monto'],
                        'estado' => $adicional['estado'] ?? 'activo',
                    ]);
                } else {
                    $item->adicionales()->create([
                        'nombre' => $adicional['nombre'],
                        'monto' => $adicional['monto'],
                        'estado' => 'activo',
                    ]);
                }
            }
        });

        return redirect()->route('admin.items.index')->with('mensaje', 'Ítem actualizado correctamente.');
    }
}
