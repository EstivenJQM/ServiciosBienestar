<?php

namespace App\Http\Controllers;

use App\Services\CargaEstudiantesService;
use App\Models\Periodo;
use Illuminate\Http\Request;

class CargaUsuariosController extends Controller
{
    private const ROLES_CON_PLAN = ['Estudiante', 'Graduado'];

    public function index()
    {
        $periodos = Periodo::orderByDesc('nombre')->get();

        return view('usuarios.carga', compact('periodos'));
    }

    public function store(Request $request, CargaEstudiantesService $service)
    {
        $request->validate([
            'nombre_rol' => 'required|in:Estudiante,Graduado',
            'id_periodo' => 'required|exists:periodo,id_periodo',
            'archivo'    => 'required|file|mimes:csv,txt|max:20480',
        ], [
            'nombre_rol.required' => 'Seleccione el tipo de usuario a cargar.',
            'nombre_rol.in'       => 'El tipo de usuario seleccionado no es válido.',
            'id_periodo.required' => 'Seleccione un período.',
            'id_periodo.exists'   => 'El período seleccionado no existe.',
            'archivo.required'    => 'Seleccione un archivo CSV.',
            'archivo.mimes'       => 'El archivo debe ser CSV (.csv).',
            'archivo.max'         => 'El archivo no debe superar los 20 MB.',
        ]);

        $resultado = $service->cargar(
            $request->file('archivo'),
            (int) $request->id_periodo,
            $request->nombre_rol
        );

        return back()
            ->with('resultado', $resultado)
            ->with('nombre_rol', $request->nombre_rol);
    }
}
