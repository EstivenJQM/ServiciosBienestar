<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use Illuminate\Http\Request;

class DependenciaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim($request->get('busqueda', ''));

        $dependencias = Dependencia::when($busqueda, fn($q) => $q->where('nombre', 'like', "%{$busqueda}%"))
            ->orderBy('nombre')
            ->get();

        return view('dependencias.index', compact('dependencias', 'busqueda'));
    }

    public function create()
    {
        return view('dependencias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150|unique:dependencia,nombre',
        ], [
            'nombre.required' => 'El nombre de la dependencia es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'   => 'Ya existe una dependencia con ese nombre.',
        ]);

        Dependencia::create([
            'nombre' => mb_strtoupper(trim($request->nombre)),
        ]);

        return redirect()->route('dependencias.index')
            ->with('success', 'Dependencia creada correctamente.');
    }

    public function edit(Dependencia $dependencia)
    {
        return view('dependencias.edit', compact('dependencia'));
    }

    public function update(Request $request, Dependencia $dependencia)
    {
        $request->validate([
            'nombre' => 'required|string|max:150|unique:dependencia,nombre,' . $dependencia->id_dependencia . ',id_dependencia',
        ], [
            'nombre.required' => 'El nombre de la dependencia es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'   => 'Ya existe una dependencia con ese nombre.',
        ]);

        $dependencia->update([
            'nombre' => mb_strtoupper(trim($request->nombre)),
        ]);

        return redirect()->route('dependencias.index')
            ->with('success', 'Dependencia actualizada correctamente.');
    }

    public function destroy(Dependencia $dependencia)
    {
        $dependencia->delete();

        return redirect()->route('dependencias.index')
            ->with('success', 'Dependencia eliminada correctamente.');
    }
}
