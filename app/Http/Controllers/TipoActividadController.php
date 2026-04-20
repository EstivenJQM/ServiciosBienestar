<?php

namespace App\Http\Controllers;

use App\Models\Componente;
use App\Models\TipoActividad;
use Illuminate\Http\Request;

class TipoActividadController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim($request->get('busqueda', ''));

        $tiposActividad = TipoActividad::with([
            'lineas.componente.area',
        ])
            ->when($busqueda, fn($q) => $q->where('nombre', 'like', "%{$busqueda}%"))
            ->orderBy('nombre')
            ->get();

        return view('tipo_actividad.index', compact('tiposActividad', 'busqueda'));
    }

    public function create()
    {
        $componentes = Componente::with([
            'area',
            'lineas' => fn($q) => $q->orderBy('nombre'),
        ])
            ->orderBy('id_area')
            ->orderBy('nombre')
            ->get();

        return view('tipo_actividad.create', compact('componentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150|unique:tipo_actividad,nombre',
            'lineas'   => 'required|array|min:1',
            'lineas.*' => 'exists:linea,id_linea',
        ], [
            'nombre.required' => 'El nombre del tipo de actividad es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'   => 'Ya existe un tipo de actividad con ese nombre.',
            'lineas.required' => 'Debe asociar al menos una línea.',
            'lineas.min'      => 'Debe asociar al menos una línea.',
            'lineas.*.exists' => 'Una de las líneas seleccionadas no existe.',
        ]);

        $tipoActividad = TipoActividad::create(['nombre' => $request->nombre]);
        $tipoActividad->lineas()->sync($request->lineas);

        return redirect()->route('tipo-actividad.index')
            ->with('success', 'Tipo de actividad creado correctamente.');
    }

    public function edit(TipoActividad $tipoActividad)
    {
        $componentes = Componente::with([
            'area',
            'lineas' => fn($q) => $q->orderBy('nombre'),
        ])
            ->orderBy('id_area')
            ->orderBy('nombre')
            ->get();

        $lineasSeleccionadas = $tipoActividad->lineas->pluck('id_linea')->toArray();

        return view('tipo_actividad.edit', compact('tipoActividad', 'componentes', 'lineasSeleccionadas'));
    }

    public function update(Request $request, TipoActividad $tipoActividad)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150|unique:tipo_actividad,nombre,' . $tipoActividad->id_tipo_actividad . ',id_tipo_actividad',
            'lineas'   => 'required|array|min:1',
            'lineas.*' => 'exists:linea,id_linea',
        ], [
            'nombre.required' => 'El nombre del tipo de actividad es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'   => 'Ya existe un tipo de actividad con ese nombre.',
            'lineas.required' => 'Debe asociar al menos una línea.',
            'lineas.min'      => 'Debe asociar al menos una línea.',
            'lineas.*.exists' => 'Una de las líneas seleccionadas no existe.',
        ]);

        $tipoActividad->update(['nombre' => $request->nombre]);
        $tipoActividad->lineas()->sync($request->lineas);

        return redirect()->route('tipo-actividad.index')
            ->with('success', 'Tipo de actividad actualizado correctamente.');
    }

    public function destroy(TipoActividad $tipoActividad)
    {
        $tipoActividad->lineas()->detach();
        $tipoActividad->delete();

        return redirect()->route('tipo-actividad.index')
            ->with('success', 'Tipo de actividad eliminado correctamente.');
    }
}
