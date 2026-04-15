<?php

namespace App\Http\Controllers;

use App\Services\CargaEstudiantesService;
use App\Models\Periodo;
use Illuminate\Http\Request;

class CargaEstudiantesController extends Controller
{
    public function index()
    {
        $periodos = Periodo::orderByDesc('nombre')->get();

        return view('usuarios.carga-estudiantes', compact('periodos'));
    }

    public function store(Request $request, CargaEstudiantesService $service)
    {
        $request->validate([
            'id_periodo' => 'required|exists:periodo,id_periodo',
            'archivo'    => 'required|file|mimes:csv,txt|max:20480',
        ], [
            'id_periodo.required' => 'Seleccione un período.',
            'id_periodo.exists'   => 'El período seleccionado no existe.',
            'archivo.required'    => 'Seleccione un archivo CSV.',
            'archivo.mimes'       => 'El archivo debe ser CSV (.csv).',
            'archivo.max'         => 'El archivo no debe superar los 20 MB.',
        ]);

        $resultado = $service->cargar(
            $request->file('archivo'),
            (int) $request->id_periodo
        );

        return back()->with('resultado', $resultado);
    }
}
