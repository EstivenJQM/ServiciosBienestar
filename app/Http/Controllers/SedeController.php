<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use Illuminate\Http\Request;

class SedeController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim($request->get('busqueda', ''));

        $sedes = Sede::when($busqueda, fn($q) => $q->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('codigo', 'like', "%{$busqueda}%");
            }))
            ->orderBy('nombre')
            ->get();

        return view('sedes.index', compact('sedes', 'busqueda'));
    }

    public function create()
    {
        return view('sedes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:10|unique:sede,codigo',
            'nombre' => 'required|string|max:100',
        ], [
            'codigo.required' => 'El código de la sede es obligatorio.',
            'codigo.max'      => 'El código no puede superar los 10 caracteres.',
            'codigo.unique'   => 'Ya existe una sede con ese código.',
            'nombre.required' => 'El nombre de la sede es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        Sede::create([
            'codigo' => strtoupper(trim($request->codigo)),
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('sedes.index')
            ->with('success', 'Sede creada correctamente.');
    }

    public function edit(Sede $sede)
    {
        return view('sedes.edit', compact('sede'));
    }

    public function update(Request $request, Sede $sede)
    {
        $request->validate([
            'codigo' => 'required|string|max:10|unique:sede,codigo,' . $sede->id_sede . ',id_sede',
            'nombre' => 'required|string|max:100',
        ], [
            'codigo.required' => 'El código de la sede es obligatorio.',
            'codigo.max'      => 'El código no puede superar los 10 caracteres.',
            'codigo.unique'   => 'Ya existe una sede con ese código.',
            'nombre.required' => 'El nombre de la sede es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
        ]);

        $sede->update([
            'codigo' => strtoupper(trim($request->codigo)),
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('sedes.index')
            ->with('success', 'Sede actualizada correctamente.');
    }

    public function destroy(Sede $sede)
    {
        $sede->facultades()->detach();
        $sede->delete();

        return redirect()->route('sedes.index')
            ->with('success', 'Sede eliminada correctamente.');
    }
}
