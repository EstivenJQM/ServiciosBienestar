<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim($request->get('busqueda', ''));

        $cargos = Cargo::when($busqueda, fn($q) => $q
                ->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('codigo', 'like', "%{$busqueda}%"))
            ->orderBy('codigo')
            ->orderBy('nombre')
            ->get();

        return view('cargos.index', compact('cargos', 'busqueda'));
    }

    public function create()
    {
        return view('cargos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:30|unique:cargo,codigo',
            'nombre' => 'required|string|max:150|unique:cargo,nombre',
        ], [
            'codigo.required' => 'El código del cargo es obligatorio.',
            'codigo.max'      => 'El código no puede superar los 30 caracteres.',
            'codigo.unique'   => 'Ya existe un cargo con ese código.',
            'nombre.required' => 'El nombre del cargo es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'   => 'Ya existe un cargo con ese nombre.',
        ]);

        Cargo::create([
            'codigo' => mb_strtoupper(trim($request->codigo)),
            'nombre' => mb_strtoupper(trim($request->nombre)),
        ]);

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo creado correctamente.');
    }

    public function edit(Cargo $cargo)
    {
        return view('cargos.edit', compact('cargo'));
    }

    public function update(Request $request, Cargo $cargo)
    {
        $request->validate([
            'codigo' => 'required|string|max:30|unique:cargo,codigo,' . $cargo->id_cargo . ',id_cargo',
            'nombre' => 'required|string|max:150|unique:cargo,nombre,' . $cargo->id_cargo . ',id_cargo',
        ], [
            'codigo.required' => 'El código del cargo es obligatorio.',
            'codigo.max'      => 'El código no puede superar los 30 caracteres.',
            'codigo.unique'   => 'Ya existe un cargo con ese código.',
            'nombre.required' => 'El nombre del cargo es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique'   => 'Ya existe un cargo con ese nombre.',
        ]);

        $cargo->update([
            'codigo' => mb_strtoupper(trim($request->codigo)),
            'nombre' => mb_strtoupper(trim($request->nombre)),
        ]);

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo actualizado correctamente.');
    }

    public function destroy(Cargo $cargo)
    {
        $cargo->delete();

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo eliminado correctamente.');
    }
}
