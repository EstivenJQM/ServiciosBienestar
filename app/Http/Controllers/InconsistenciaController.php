<?php

namespace App\Http\Controllers;

use App\Models\CargaInconsistencia;
use App\Models\Periodo;
use App\Models\Sede;
use App\Services\CargaEstudiantesService;
use App\Services\CargaContratistasService;
use App\Services\CargaFamiliaresService;
use App\Services\CargaAdministrativosService;
use Illuminate\Http\Request;

class InconsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $periodos  = Periodo::orderByDesc('nombre')->get();
        $idPeriodo = $request->get('id_periodo');

        $query = CargaInconsistencia::with('periodo')
            ->latest('updated_at');

        if ($idPeriodo) {
            $query->where('id_periodo', $idPeriodo);
        }

        $inconsistencias = $query->paginate(25)->withQueryString();
        $total           = CargaInconsistencia::count();

        return view('usuarios.inconsistencias.index',
            compact('inconsistencias', 'periodos', 'idPeriodo', 'total'));
    }

    public function edit(CargaInconsistencia $inconsistencia)
    {
        $sedes   = Sede::orderBy('nombre')->get();
        $periodos = Periodo::orderByDesc('nombre')->get();

        return view('usuarios.inconsistencias.edit',
            compact('inconsistencia', 'sedes', 'periodos'));
    }

    public function update(
        Request $request,
        CargaInconsistencia         $inconsistencia,
        CargaEstudiantesService     $estudiantesService,
        CargaContratistasService    $contratistasService,
        CargaFamiliaresService      $familiaresService,
        CargaAdministrativosService $administrativosService
    ) {
        $esAdministrativo = ($inconsistencia->nombre_rol === 'Administrativo');
        $esContratista    = ($inconsistencia->nombre_rol === 'Contratista');
        $esFamiliar       = ($inconsistencia->nombre_rol === 'Familiar');

        if ($esAdministrativo) {
            $request->validate([
                'id_periodo'   => 'required|exists:periodo,id_periodo',
                'documento'    => 'required|string|max:20',
                'nombres'      => 'required|string|max:100',
                'apellidos'    => 'required|string|max:100',
                'email'        => 'required|string|max:100',
                'nombre_sede'  => 'required|string|max:100',
                'dependencia'  => 'required|string|max:200',
                'codigo_cargo' => 'required|string|max:30',
                'nombre_cargo' => 'required|string|max:150',
            ], [
                'id_periodo.required'   => 'Seleccione un período.',
                'documento.required'    => 'El documento es obligatorio.',
                'nombres.required'      => 'Los nombres son obligatorios.',
                'apellidos.required'    => 'Los apellidos son obligatorios.',
                'email.required'        => 'El correo es obligatorio.',
                'nombre_sede.required'  => 'La sede es obligatoria.',
                'dependencia.required'  => 'La dependencia es obligatoria.',
                'codigo_cargo.required' => 'El código del cargo es obligatorio.',
                'nombre_cargo.required' => 'El nombre del cargo es obligatorio.',
            ]);
        } elseif ($esContratista) {
            $request->validate([
                'id_periodo'  => 'required|exists:periodo,id_periodo',
                'documento'   => 'required|string|max:20',
                'nombres'     => 'required|string|max:100',
                'apellidos'   => 'required|string|max:100',
                'email'       => 'required|string|max:100',
                'nombre_sede' => 'required|string|max:100',
                'dependencia' => 'required|string|max:200',
            ], [
                'id_periodo.required'  => 'Seleccione un período.',
                'documento.required'   => 'El documento es obligatorio.',
                'nombres.required'     => 'Los nombres son obligatorios.',
                'apellidos.required'   => 'Los apellidos son obligatorios.',
                'email.required'       => 'El correo es obligatorio.',
                'nombre_sede.required' => 'La sede es obligatoria.',
                'dependencia.required' => 'La dependencia es obligatoria.',
            ]);
        } elseif ($esFamiliar) {
            $request->validate([
                'id_periodo'  => 'required|exists:periodo,id_periodo',
                'documento'   => 'required|string|max:20',
                'nombres'     => 'required|string|max:100',
                'apellidos'   => 'required|string|max:100',
                'email'       => 'required|string|max:100',
                'nombre_sede' => 'required|string|max:100',
            ], [
                'id_periodo.required'  => 'Seleccione un período.',
                'documento.required'   => 'El documento es obligatorio.',
                'nombres.required'     => 'Los nombres son obligatorios.',
                'apellidos.required'   => 'Los apellidos son obligatorios.',
                'email.required'       => 'El correo es obligatorio.',
                'nombre_sede.required' => 'La sede es obligatoria.',
            ]);
        } else {
            $request->validate([
                'id_periodo'      => 'required|exists:periodo,id_periodo',
                'documento'       => 'required|string|max:20',
                'nombres'         => 'required|string|max:100',
                'apellidos'       => 'required|string|max:100',
                'email'           => 'required|string|max:100',
                'codigo_sede'     => 'required|string|max:10',
                'nombre_sede'     => 'required|string|max:100',
                'codigo_plan'     => 'required|string|max:20',
                'nombre_programa' => 'required|string|max:200',
                'nombre_facultad' => 'required|string|max:200',
            ], [
                'id_periodo.required'      => 'Seleccione un período.',
                'id_periodo.exists'        => 'El período no existe.',
                'documento.required'       => 'El documento es obligatorio.',
                'nombres.required'         => 'Los nombres son obligatorios.',
                'apellidos.required'       => 'Los apellidos son obligatorios.',
                'codigo_sede.required'     => 'El código de sede es obligatorio.',
                'nombre_sede.required'     => 'El nombre de sede es obligatorio.',
                'codigo_plan.required'     => 'El código de plan es obligatorio.',
                'nombre_programa.required' => 'El nombre del programa es obligatorio.',
                'nombre_facultad.required' => 'El nombre de la facultad es obligatorio.',
                'email.required'           => 'El email es obligatorio.',
            ]);
        }

        if ($esAdministrativo) {
            $inconsistencia->fill($request->only([
                'id_periodo', 'documento', 'nombres', 'apellidos',
                'email', 'nombre_sede', 'dependencia', 'codigo_cargo', 'nombre_cargo',
            ]));
        } elseif ($esContratista) {
            $inconsistencia->fill($request->only([
                'id_periodo', 'documento', 'nombres', 'apellidos',
                'email', 'nombre_sede', 'dependencia',
            ]));
        } elseif ($esFamiliar) {
            $inconsistencia->fill($request->only([
                'id_periodo', 'documento', 'nombres', 'apellidos',
                'email', 'nombre_sede',
            ]));
        } else {
            $inconsistencia->fill($request->only([
                'id_periodo', 'documento', 'nombres', 'apellidos', 'email',
                'codigo_sede', 'nombre_sede', 'codigo_plan',
                'nombre_programa', 'nombre_facultad',
            ]));
        }

        if ($esAdministrativo) {
            [$ok, $error] = $administrativosService->procesarFilaIndividual(
                (int) $request->id_periodo,
                trim($request->documento),
                trim($request->nombres),
                trim($request->apellidos),
                trim($request->email ?? ''),
                trim($request->nombre_sede),
                trim($request->dependencia),
                trim($request->codigo_cargo),
                trim($request->nombre_cargo),
            );
        } elseif ($esContratista) {
            [$ok, $error] = $contratistasService->procesarFilaIndividual(
                (int) $request->id_periodo,
                trim($request->documento),
                trim($request->nombres),
                trim($request->apellidos),
                trim($request->email ?? ''),
                trim($request->nombre_sede),
                trim($request->dependencia),
            );
        } elseif ($esFamiliar) {
            [$ok, $error] = $familiaresService->procesarFilaIndividual(
                (int) $request->id_periodo,
                trim($request->documento),
                trim($request->nombres),
                trim($request->apellidos),
                trim($request->email ?? ''),
                trim($request->nombre_sede),
            );
        } else {
            [$ok, $error] = $estudiantesService->procesarFilaIndividual(
                (int) $request->id_periodo,
                trim($request->documento),
                trim($request->nombres),
                trim($request->apellidos),
                trim($request->email ?? ''),
                trim($request->codigo_sede),
                trim($request->nombre_sede),
                trim($request->codigo_plan),
                trim($request->nombre_programa),
                trim($request->nombre_facultad),
                $inconsistencia->nombre_rol ?? 'Estudiante',
            );
        }

        if ($ok) {
            $inconsistencia->delete();
            return redirect()->route('usuarios.inconsistencias.index')
                ->with('success', 'Registro corregido y guardado correctamente.');
        }

        // Guardar los datos corregidos y el nuevo error
        $inconsistencia->error = $error;
        $inconsistencia->save();

        return back()
            ->withInput()
            ->with('error', 'No se pudo procesar el registro: ' . $error);
    }

    public function destroy(CargaInconsistencia $inconsistencia)
    {
        $inconsistencia->delete();
        return back()->with('success', 'Inconsistencia eliminada.');
    }

    public function destroyAll(Request $request)
    {
        $idPeriodo = $request->id_periodo;

        $count = CargaInconsistencia::when($idPeriodo, fn($q) => $q->where('id_periodo', $idPeriodo))->count();
        CargaInconsistencia::when($idPeriodo, fn($q) => $q->where('id_periodo', $idPeriodo))->delete();

        return redirect()->route('usuarios.inconsistencias.index')
            ->with('success', "{$count} inconsistencia(s) eliminada(s).");
    }
}
