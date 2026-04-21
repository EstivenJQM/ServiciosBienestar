<?php

namespace App\Http\Controllers;

use App\Services\CargaEstudiantesService;
use App\Services\CargaContratistasService;
use App\Services\CargaFamiliaresService;
use App\Models\Periodo;
use Illuminate\Http\Request;

class CargaUsuariosController extends Controller
{
    public function index()
    {
        $periodos = Periodo::orderByDesc('nombre')->get();

        return view('usuarios.carga', compact('periodos'));
    }

    public function store(
        Request $request,
        CargaEstudiantesService  $estudiantesService,
        CargaContratistasService $contratistasService,
        CargaFamiliaresService   $familiaresService
    ) {
        $request->validate([
            'nombre_rol' => 'required|in:Estudiante,Graduado,Contratista,Familiar',
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

        $rol = $request->nombre_rol;

        if ($rol === 'Contratista') {
            $resultado = $contratistasService->cargar(
                $request->file('archivo'),
                (int) $request->id_periodo
            );
        } elseif ($rol === 'Familiar') {
            $resultado = $familiaresService->cargar(
                $request->file('archivo'),
                (int) $request->id_periodo
            );
        } else {
            $resultado = $estudiantesService->cargar(
                $request->file('archivo'),
                (int) $request->id_periodo,
                $rol
            );
        }

        return back()
            ->with('resultado', $resultado)
            ->with('nombre_rol', $rol);
    }
}
