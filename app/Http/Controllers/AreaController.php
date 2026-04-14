<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::with([
                'componentes'                        => fn($q) => $q->orderBy('nombre'),
                'componentes.lineas'                 => fn($q) => $q->orderBy('nombre'),
                'componentes.lineas.tiposActividad'  => fn($q) => $q->orderBy('nombre'),
            ])
            ->orderBy('nombre')
            ->get();

        return view('areas.index', compact('areas'));
    }

    public function create()
    {
        return view('areas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150|unique:area,nombre',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'   => 'Ya existe un área con ese nombre.',
        ]);

        Area::create(['nombre' => $request->nombre]);

        return redirect()->route('areas.index')
            ->with('success', 'Área creada correctamente.');
    }

    public function edit(Area $area)
    {
        return view('areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area)
    {
        $request->validate([
            'nombre' => 'required|string|max:150|unique:area,nombre,' . $area->id_area . ',id_area',
        ], [
            'nombre.required' => 'El nombre del área es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'   => 'Ya existe un área con ese nombre.',
        ]);

        $area->update(['nombre' => $request->nombre]);

        return redirect()->route('areas.index')
            ->with('success', 'Área actualizada correctamente.');
    }

    public function destroy(Area $area)
    {
        $area->delete();

        return redirect()->route('areas.index')
            ->with('success', 'Área eliminada correctamente.');
    }
}
