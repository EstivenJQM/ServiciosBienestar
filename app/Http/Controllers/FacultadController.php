<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use App\Models\Sede;
use Illuminate\Http\Request;

class FacultadController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim($request->get('busqueda', ''));

        $facultades = Facultad::with(['sedes' => fn($q) => $q->orderBy('nombre')])
            ->when($busqueda, fn($q) => $q->where('nombre', 'like', "%{$busqueda}%"))
            ->orderBy('nombre')
            ->get();

        return view('facultades.index', compact('facultades', 'busqueda'));
    }

    public function create()
    {
        $sedes = Sede::orderBy('nombre')->get();
        return view('facultades.create', compact('sedes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150|unique:facultad,nombre',
            'sedes'    => 'required|array|min:1',
            'sedes.*'  => 'exists:sede,id_sede',
        ], [
            'nombre.required'  => 'El nombre de la facultad es obligatorio.',
            'nombre.max'       => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'    => 'Ya existe una facultad con ese nombre.',
            'sedes.required'   => 'Debe asociar al menos una sede.',
            'sedes.min'        => 'Debe asociar al menos una sede.',
            'sedes.*.exists'   => 'Una de las sedes seleccionadas no existe.',
        ]);

        $facultad = Facultad::create(['nombre' => $request->nombre]);
        $facultad->sedes()->sync($request->sedes);

        return redirect()->route('facultades.index')
            ->with('success', 'Facultad creada correctamente.');
    }

    public function edit(Facultad $facultad)
    {
        $sedes = Sede::orderBy('nombre')->get();
        $sedesSeleccionadas = $facultad->sedes->pluck('id_sede')->toArray();

        return view('facultades.edit', compact('facultad', 'sedes', 'sedesSeleccionadas'));
    }

    public function update(Request $request, Facultad $facultad)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150|unique:facultad,nombre,' . $facultad->id_facultad . ',id_facultad',
            'sedes'    => 'required|array|min:1',
            'sedes.*'  => 'exists:sede,id_sede',
        ], [
            'nombre.required'  => 'El nombre de la facultad es obligatorio.',
            'nombre.max'       => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'    => 'Ya existe una facultad con ese nombre.',
            'sedes.required'   => 'Debe asociar al menos una sede.',
            'sedes.min'        => 'Debe asociar al menos una sede.',
            'sedes.*.exists'   => 'Una de las sedes seleccionadas no existe.',
        ]);

        $facultad->update(['nombre' => $request->nombre]);
        $facultad->sedes()->sync($request->sedes);

        return redirect()->route('facultades.index')
            ->with('success', 'Facultad actualizada correctamente.');
    }

    public function destroy(Facultad $facultad)
    {
        $facultad->sedes()->detach();
        $facultad->delete();

        return redirect()->route('facultades.index')
            ->with('success', 'Facultad eliminada correctamente.');
    }
}
