<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Componente;
use App\Models\Linea;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LineaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim($request->get('busqueda', ''));

        $lineas = Linea::with(['componente.area', 'tiposActividad' => fn($q) => $q->orderBy('nombre')])
            ->when($busqueda, fn($q) => $q->where('nombre', 'like', "%{$busqueda}%")
                ->orWhereHas('componente', fn($q) => $q->where('nombre', 'like', "%{$busqueda}%"))
            )
            ->orderBy('id_componente')
            ->orderBy('nombre')
            ->get()
            ->groupBy('id_componente');

        $componentes = Componente::with('area')
            ->orderBy('id_area')
            ->orderBy('nombre')
            ->get()
            ->keyBy('id_componente');

        return view('lineas.index', compact('lineas', 'componentes', 'busqueda'));
    }

    public function create()
    {
        $componentes = Componente::with('area')
            ->orderBy('id_area')
            ->orderBy('nombre')
            ->get();

        return view('lineas.create', compact('componentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_componente' => 'required|exists:componente,id_componente',
            'nombre'        => [
                'required', 'string', 'max:150',
                Rule::unique('linea')->where('id_componente', $request->id_componente),
            ],
        ], [
            'id_componente.required' => 'Seleccione un componente.',
            'id_componente.exists'   => 'El componente seleccionado no existe.',
            'nombre.required'        => 'El nombre de la línea es obligatorio.',
            'nombre.max'             => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'          => 'Ya existe una línea con ese nombre en el componente seleccionado.',
        ]);

        Linea::create([
            'id_componente' => $request->id_componente,
            'nombre'        => $request->nombre,
        ]);

        return redirect()->route('lineas.index')
            ->with('success', 'Línea creada correctamente.');
    }

    public function edit(Linea $linea)
    {
        $componentes = Componente::with('area')
            ->orderBy('id_area')
            ->orderBy('nombre')
            ->get();

        return view('lineas.edit', compact('linea', 'componentes'));
    }

    public function update(Request $request, Linea $linea)
    {
        $request->validate([
            'id_componente' => 'required|exists:componente,id_componente',
            'nombre'        => [
                'required', 'string', 'max:150',
                Rule::unique('linea')
                    ->where('id_componente', $request->id_componente)
                    ->ignore($linea->id_linea, 'id_linea'),
            ],
        ], [
            'id_componente.required' => 'Seleccione un componente.',
            'id_componente.exists'   => 'El componente seleccionado no existe.',
            'nombre.required'        => 'El nombre de la línea es obligatorio.',
            'nombre.max'             => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'          => 'Ya existe una línea con ese nombre en el componente seleccionado.',
        ]);

        $linea->update([
            'id_componente' => $request->id_componente,
            'nombre'        => $request->nombre,
        ]);

        return redirect()->route('lineas.index')
            ->with('success', 'Línea actualizada correctamente.');
    }

    public function destroy(Linea $linea)
    {
        $linea->delete();

        return redirect()->route('lineas.index')
            ->with('success', 'Línea eliminada correctamente.');
    }
}
