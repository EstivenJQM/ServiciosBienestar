<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Componente;
use Illuminate\Http\Request;

class ComponenteController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim($request->get('busqueda', ''));

        $componentes = Componente::with([
            'area',
            'lineas'                => fn($q) => $q->orderBy('nombre'),
            'lineas.tiposActividad' => fn($q) => $q->orderBy('nombre'),
        ])
            ->when($busqueda, fn($q) => $q->where('nombre', 'like', "%{$busqueda}%")
                ->orWhereHas('area', fn($q) => $q->where('nombre', 'like', "%{$busqueda}%"))
            )
            ->orderBy('id_area')
            ->orderBy('nombre')
            ->get()
            ->groupBy('id_area');

        $areas = Area::orderBy('nombre')->get()->keyBy('id_area');

        return view('componentes.index', compact('componentes', 'areas', 'busqueda'));
    }

    public function create()
    {
        $areas = Area::orderBy('nombre')->get();
        return view('componentes.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_area' => 'required|exists:area,id_area',
            'nombre'  => [
                'required', 'string', 'max:150',
                \Illuminate\Validation\Rule::unique('componente')->where('id_area', $request->id_area),
            ],
        ], [
            'id_area.required' => 'Seleccione un área.',
            'id_area.exists'   => 'El área seleccionada no existe.',
            'nombre.required'  => 'El nombre del componente es obligatorio.',
            'nombre.max'       => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'    => 'Ya existe un componente con ese nombre en el área seleccionada.',
        ]);

        Componente::create([
            'id_area' => $request->id_area,
            'nombre'  => $request->nombre,
        ]);

        return redirect()->route('componentes.index')
            ->with('success', 'Componente creado correctamente.');
    }

    public function edit(Componente $componente)
    {
        $areas = Area::orderBy('nombre')->get();
        return view('componentes.edit', compact('componente', 'areas'));
    }

    public function update(Request $request, Componente $componente)
    {
        $request->validate([
            'id_area' => 'required|exists:area,id_area',
            'nombre'  => [
                'required', 'string', 'max:150',
                \Illuminate\Validation\Rule::unique('componente')
                    ->where('id_area', $request->id_area)
                    ->ignore($componente->id_componente, 'id_componente'),
            ],
        ], [
            'id_area.required' => 'Seleccione un área.',
            'id_area.exists'   => 'El área seleccionada no existe.',
            'nombre.required'  => 'El nombre del componente es obligatorio.',
            'nombre.max'       => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'    => 'Ya existe un componente con ese nombre en el área seleccionada.',
        ]);

        $componente->update([
            'id_area' => $request->id_area,
            'nombre'  => $request->nombre,
        ]);

        return redirect()->route('componentes.index')
            ->with('success', 'Componente actualizado correctamente.');
    }

    public function destroy(Componente $componente)
    {
        $componente->delete();

        return redirect()->route('componentes.index')
            ->with('success', 'Componente eliminado correctamente.');
    }
}
