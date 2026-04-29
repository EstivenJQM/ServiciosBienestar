<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Cargo;
use App\Models\Componente;
use App\Models\Dependencia;
use App\Models\Facultad;
use App\Models\Linea;
use App\Models\Periodo;
use App\Models\PlanEstudio;
use App\Models\Programa;
use App\Models\Sede;
use App\Models\Servicio;
use App\Models\TipoActividad;
use App\Models\TipoEmpleado;
use App\Services\CargaServicioUsuariosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServicioController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────

    private function parseArrayInput(Request $request, string $key): array
    {
        return array_values(array_filter((array) $request->input($key, [])));
    }

    private function applyServiceFilters($query, array $f): void
    {
        if ($f['busqueda'] !== '') {
            $query->where('nombre', 'like', "%{$f['busqueda']}%");
        }
        if (!empty($f['nombresServicios'])) $query->whereIn('nombre', $f['nombresServicios']);
        if (!empty($f['idPeriodos']))       $query->whereIn('id_periodo', $f['idPeriodos']);
        if (!empty($f['idSedes']))          $query->whereIn('id_sede', $f['idSedes']);
        if (!empty($f['idAreas']))          $query->whereHas('linea.componente', fn($q) => $q->whereIn('id_area', $f['idAreas']));
        if (!empty($f['idComponentes']))    $query->whereHas('linea', fn($q) => $q->whereIn('id_componente', $f['idComponentes']));
        if (!empty($f['idLineas']))         $query->whereIn('id_linea', $f['idLineas']);
        if (!empty($f['idTiposActividad'])) $query->whereIn('id_tipo_actividad', $f['idTiposActividad']);
        if ($f['fechaDesde'])               $query->where('fecha', '>=', $f['fechaDesde']);
        if ($f['fechaHasta'])               $query->where('fecha', '<=', $f['fechaHasta']);
    }

    private function buildBeneficiaryFilter(array $b): \Closure
    {
        return function ($q) use ($b) {
            ['roles' => $roles, 'idSedesBenef' => $idSedesBenef,
             'idFacultades' => $idFacultades, 'idProgramas' => $idProgramas,
             'idPlanes' => $idPlanes, 'tiposEmpleado' => $tiposEmpleado,
             'idDependencias' => $idDependencias, 'idCargos' => $idCargos] = $b;

            if (!empty($roles)) {
                $q->whereHas('rol', fn($r) => $r->whereIn('nombre', $roles));
            }

            if (!empty($idSedesBenef)) {
                $q->whereIn('id_sede', $idSedesBenef);
            }

            $hasStudent  = !empty($idFacultades) || !empty($idProgramas) || !empty($idPlanes);
            $hasEmployee = !empty($tiposEmpleado) || !empty($idDependencias) || !empty($idCargos);

            if ($hasStudent || $hasEmployee) {
                $q->where(function ($outer) use ($hasStudent, $hasEmployee, $idFacultades, $idProgramas, $idPlanes, $tiposEmpleado, $idDependencias, $idCargos) {
                    if ($hasStudent) {
                        $outer->whereHas('estudianteEgresado', fn($ee) =>
                            $ee->whereHas('planEstudio', function ($pl) use ($idFacultades, $idProgramas, $idPlanes) {
                                if (!empty($idPlanes)) {
                                    $pl->whereIn('id_plan_estudio', $idPlanes);
                                }
                                if (!empty($idProgramas) || !empty($idFacultades)) {
                                    $pl->whereHas('programaSede', function ($ps) use ($idFacultades, $idProgramas) {
                                        if (!empty($idProgramas)) $ps->whereIn('id_programa', $idProgramas);
                                        if (!empty($idFacultades)) $ps->whereHas('programa', fn($pr) => $pr->whereIn('id_facultad', $idFacultades));
                                    });
                                }
                            })
                        );
                    }
                    if ($hasEmployee) {
                        $method = $hasStudent ? 'orWhereHas' : 'whereHas';
                        $outer->$method('empleado', function ($emp) use ($tiposEmpleado, $idDependencias, $idCargos) {
                            if (!empty($tiposEmpleado)) $emp->whereHas('tipoEmpleado', fn($t) => $t->whereIn('nombre', $tiposEmpleado));
                            if (!empty($idDependencias)) $emp->whereIn('id_dependencia', $idDependencias);
                            if (!empty($idCargos))       $emp->whereIn('id_cargo', $idCargos);
                        });
                    }
                });
            }
        };
    }

    private function parseServiceFilters(Request $request): array
    {
        return [
            'busqueda'         => trim($request->input('q', '')),
            'nombresServicios' => array_values(array_filter($request->input('nombre_servicio', []))),
            'idPeriodos'       => $this->parseArrayInput($request, 'id_periodo'),
            'idSedes'         => $this->parseArrayInput($request, 'id_sede'),
            'idAreas'         => $this->parseArrayInput($request, 'id_area'),
            'idComponentes'   => $this->parseArrayInput($request, 'id_componente'),
            'idLineas'        => $this->parseArrayInput($request, 'id_linea'),
            'idTiposActividad'=> $this->parseArrayInput($request, 'id_tipo_actividad'),
            'fechaDesde'      => $request->input('fecha_desde'),
            'fechaHasta'      => $request->input('fecha_hasta'),
        ];
    }

    private function parseBeneficiaryFilters(Request $request): array
    {
        return [
            'roles'          => array_values(array_filter($request->input('roles', []))),
            'idSedesBenef'   => $this->parseArrayInput($request, 'id_sede_benef'),
            'idFacultades'   => $this->parseArrayInput($request, 'id_facultad'),
            'idProgramas'    => $this->parseArrayInput($request, 'id_programa'),
            'idPlanes'       => $this->parseArrayInput($request, 'id_plan_estudio'),
            'tiposEmpleado'  => array_values(array_filter($request->input('tipos_empleado', []))),
            'idDependencias' => $this->parseArrayInput($request, 'id_dependencia'),
            'idCargos'       => $this->parseArrayInput($request, 'id_cargo'),
        ];
    }

    private function hasBeneficiaryFilters(array $b): bool
    {
        return !empty($b['roles']) || !empty($b['idSedesBenef']) || !empty($b['idFacultades'])
            || !empty($b['idProgramas']) || !empty($b['idPlanes']) || !empty($b['tiposEmpleado'])
            || !empty($b['idDependencias']) || !empty($b['idCargos']);
    }

    private function computeHojasDisponibles(array $b): array
    {
        $roles    = $b['roles'];
        $tipos    = $b['tiposEmpleado'];
        $allRoles = empty($roles);
        $allTipos = empty($tipos);

        $hojas = ['resumen', 'por_servicios'];

        if ($allRoles || in_array('Estudiante', $roles)) $hojas[] = 'estudiantes';
        if ($allRoles || in_array('Graduado',   $roles)) $hojas[] = 'graduados';

        $hasEmpleado = $allRoles || in_array('Empleado', $roles);
        if ($hasEmpleado) {
            if ($allTipos || in_array('Administrativo', $tipos)) $hojas[] = 'administrativos';
            if ($allTipos || in_array('Contratista',    $tipos)) $hojas[] = 'contratistas';
            if ($allTipos || in_array('Docente',        $tipos)) $hojas[] = 'docentes';
        }

        if ($allRoles || in_array('Familiar', $roles)) $hojas[] = 'familiares';

        return $hojas;
    }

    private function dropdownData(): array
    {
        return [
            'periodos'       => Periodo::orderByDesc('nombre')->get()->keyBy('id_periodo'),
            'sedes'          => Sede::orderBy('nombre')->get(),
            'areas'          => Area::orderBy('nombre')->get(),
            'componentes'    => Componente::orderBy('nombre')->get(),
            'lineas'         => Linea::with('tiposActividad')->orderBy('nombre')->get(),
            'tiposActividad' => TipoActividad::orderBy('nombre')->get(),
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // CRUD
    // ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $f = $this->parseServiceFilters($request);

        $query = Servicio::with(['linea.componente.area', 'tipoActividad', 'sede', 'periodo'])
            ->withCount('usuariosAsignados');

        $this->applyServiceFilters($query, $f);

        $coleccion = $query->orderByDesc('fecha')->get();
        $total     = $coleccion->count();
        $servicios = $coleccion->groupBy('id_periodo');

        $dd = $this->dropdownData();

        $nFiltros = (int)(!empty($f['idPeriodos'])) + (int)(!empty($f['idSedes']))
                  + (int)(!empty($f['idAreas']))    + (int)(!empty($f['idComponentes']))
                  + (int)(!empty($f['idLineas']))   + (int)(!empty($f['idTiposActividad']))
                  + (int)($f['fechaDesde'] !== null && $f['fechaDesde'] !== '')
                  + (int)($f['fechaHasta'] !== null && $f['fechaHasta'] !== '');

        $hayFiltros = $f['busqueda'] !== '' || $nFiltros > 0;

        return view('servicios.index', array_merge($f, $dd, compact(
            'servicios', 'total', 'hayFiltros', 'nFiltros'
        )));
    }

    public function show(Servicio $servicio)
    {
        $servicio->load([
            'linea.componente.area', 'tipoActividad', 'sede', 'periodo',
            'usuariosAsignados.usuario', 'usuariosAsignados.rol',
        ]);

        return view('servicios.show', compact('servicio'));
    }

    public function create()
    {
        $lineas   = Linea::with(['componente.area', 'tiposActividad'])->orderBy('id_componente')->orderBy('nombre')->get();
        $sedes    = Sede::orderBy('nombre')->get();
        $periodos = Periodo::orderByDesc('nombre')->get();

        return view('servicios.create', compact('lineas', 'sedes', 'periodos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'            => 'required|string|max:200',
            'id_linea'          => 'required|exists:linea,id_linea',
            'id_tipo_actividad' => 'required|exists:tipo_actividad,id_tipo_actividad',
            'id_sede'           => 'required|exists:sede,id_sede',
            'fecha'             => 'required|date',
            'id_periodo'        => 'required|exists:periodo,id_periodo',
        ], [
            'nombre.required'            => 'El nombre del servicio es obligatorio.',
            'nombre.max'                 => 'El nombre no puede superar los 200 caracteres.',
            'id_linea.required'          => 'Seleccione una línea.',
            'id_linea.exists'            => 'La línea seleccionada no existe.',
            'id_tipo_actividad.required' => 'Seleccione un tipo de actividad.',
            'id_tipo_actividad.exists'   => 'El tipo de actividad seleccionado no existe.',
            'id_sede.required'           => 'Seleccione una sede.',
            'id_sede.exists'             => 'La sede seleccionada no existe.',
            'fecha.required'             => 'La fecha es obligatoria.',
            'fecha.date'                 => 'La fecha no tiene un formato válido.',
            'id_periodo.required'        => 'Seleccione un período.',
            'id_periodo.exists'          => 'El período seleccionado no existe.',
        ]);

        $combinacionValida = DB::table('linea_tipo_actividad')
            ->where('id_linea', $request->id_linea)
            ->where('id_tipo_actividad', $request->id_tipo_actividad)
            ->exists();

        if (!$combinacionValida) {
            return back()->withInput()->withErrors(['id_tipo_actividad' => 'El tipo de actividad no está asociado a la línea seleccionada.']);
        }

        Servicio::create([
            'nombre'            => $request->nombre,
            'id_linea'          => $request->id_linea,
            'id_tipo_actividad' => $request->id_tipo_actividad,
            'id_sede'           => $request->id_sede,
            'fecha'             => $request->fecha,
            'id_periodo'        => $request->id_periodo,
        ]);

        return redirect()->route('servicios.index')->with('success', 'Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio)
    {
        $lineas   = Linea::with(['componente.area', 'tiposActividad'])->orderBy('id_componente')->orderBy('nombre')->get();
        $sedes    = Sede::orderBy('nombre')->get();
        $periodos = Periodo::orderByDesc('nombre')->get();

        return view('servicios.edit', compact('servicio', 'lineas', 'sedes', 'periodos'));
    }

    public function update(Request $request, Servicio $servicio)
    {
        $request->validate([
            'nombre'            => 'required|string|max:200',
            'id_linea'          => 'required|exists:linea,id_linea',
            'id_tipo_actividad' => 'required|exists:tipo_actividad,id_tipo_actividad',
            'id_sede'           => 'required|exists:sede,id_sede',
            'fecha'             => 'required|date',
            'id_periodo'        => 'required|exists:periodo,id_periodo',
        ], [
            'nombre.required'            => 'El nombre del servicio es obligatorio.',
            'nombre.max'                 => 'El nombre no puede superar los 200 caracteres.',
            'id_linea.required'          => 'Seleccione una línea.',
            'id_linea.exists'            => 'La línea seleccionada no existe.',
            'id_tipo_actividad.required' => 'Seleccione un tipo de actividad.',
            'id_tipo_actividad.exists'   => 'El tipo de actividad seleccionado no existe.',
            'id_sede.required'           => 'Seleccione una sede.',
            'id_sede.exists'             => 'La sede seleccionada no existe.',
            'fecha.required'             => 'La fecha es obligatoria.',
            'fecha.date'                 => 'La fecha no tiene un formato válido.',
            'id_periodo.required'        => 'Seleccione un período.',
            'id_periodo.exists'          => 'El período seleccionado no existe.',
        ]);

        $combinacionValida = DB::table('linea_tipo_actividad')
            ->where('id_linea', $request->id_linea)
            ->where('id_tipo_actividad', $request->id_tipo_actividad)
            ->exists();

        if (!$combinacionValida) {
            return back()->withInput()->withErrors(['id_tipo_actividad' => 'El tipo de actividad no está asociado a la línea seleccionada.']);
        }

        $servicio->update([
            'nombre'            => $request->nombre,
            'id_linea'          => $request->id_linea,
            'id_tipo_actividad' => $request->id_tipo_actividad,
            'id_sede'           => $request->id_sede,
            'fecha'             => $request->fecha,
            'id_periodo'        => $request->id_periodo,
        ]);

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();
        return redirect()->route('servicios.index')->with('success', 'Servicio eliminado correctamente.');
    }

    public function asignarUsuarios(Request $request, Servicio $servicio, CargaServicioUsuariosService $service)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'archivo.required' => 'Seleccione un archivo CSV.',
            'archivo.mimes'    => 'El archivo debe ser .csv.',
            'archivo.max'      => 'El archivo no debe superar 5 MB.',
        ]);

        $resultado = $service->asignar($request->file('archivo'), $servicio);

        return redirect()->route('servicios.show', $servicio)->with('resultado_asignacion', $resultado);
    }

    public function desasignarUsuario(Servicio $servicio, int $idUrs)
    {
        DB::table('servicio_usuario')
            ->where('id_servicio', $servicio->id_servicio)
            ->where('id_usuario_rol_sede', $idUrs)
            ->delete();

        return back()->with('success', 'Usuario desvinculado del servicio.');
    }

    // ──────────────────────────────────────────────────────────────
    // Reportes
    // ──────────────────────────────────────────────────────────────

    public function reportes(Request $request)
    {
        $f = $this->parseServiceFilters($request);
        $b = $this->parseBeneficiaryFilters($request);

        $query = Servicio::query();
        $this->applyServiceFilters($query, $f);

        if ($this->hasBeneficiaryFilters($b)) {
            $query->whereHas('usuariosAsignados', $this->buildBeneficiaryFilter($b));
        }

        $totalServicios    = (clone $query)->count();
        $servicioIds       = (clone $query)->pluck('id_servicio');
        $totalAsignaciones = DB::table('servicio_usuario')->whereIn('id_servicio', $servicioIds)->count();

        $dd = $this->dropdownData();

        $nombresServiciosDisponibles = Servicio::select('nombre')->distinct()->orderBy('nombre')->pluck('nombre');

        $facultades         = Facultad::orderBy('nombre')->get();
        $tiposEmpleadoList  = TipoEmpleado::orderBy('nombre')->get();
        $dependencias       = Dependencia::orderBy('nombre')->get();
        $cargos             = Cargo::orderBy('nombre')->get();

        $programasDisponibles = !empty($b['idFacultades'])
            ? Programa::whereIn('id_facultad', $b['idFacultades'])->orderBy('nombre')->get()
            : collect();

        $planesDisponibles = !empty($b['idProgramas'])
            ? PlanEstudio::whereHas('programaSede', fn($q) => $q->whereIn('id_programa', $b['idProgramas']))->get()
            : collect();

        // Mapa tipo_empleado → dependencias/cargos que existen para ese tipo
        $tipoDependenciaMap = DB::table('empleado')
            ->join('tipo_empleado', 'empleado.id_tipo_empleado', '=', 'tipo_empleado.id_tipo_empleado')
            ->whereNotNull('empleado.id_dependencia')
            ->select('tipo_empleado.nombre as tipo', 'empleado.id_dependencia')
            ->distinct()->get()
            ->groupBy('tipo')
            ->map(fn($rows) => $rows->pluck('id_dependencia')->values());

        $tipoCargoMap = DB::table('empleado')
            ->join('tipo_empleado', 'empleado.id_tipo_empleado', '=', 'tipo_empleado.id_tipo_empleado')
            ->whereNotNull('empleado.id_cargo')
            ->select('tipo_empleado.nombre as tipo', 'empleado.id_cargo')
            ->distinct()->get()
            ->groupBy('tipo')
            ->map(fn($rows) => $rows->pluck('id_cargo')->values());

        // Mapa rol (Estudiante/Graduado) → facultades que tienen registros para ese rol
        $rolFacultadMap = DB::table('usuario_rol_sede')
            ->join('rol', 'usuario_rol_sede.id_rol', '=', 'rol.id_rol')
            ->join('estudiante_egresado', 'usuario_rol_sede.id_usuario_rol_sede', '=', 'estudiante_egresado.id_usuario_rol_sede')
            ->join('plan_estudio', 'estudiante_egresado.id_plan_estudio', '=', 'plan_estudio.id_plan_estudio')
            ->join('programa_sede', 'plan_estudio.id_programa_sede', '=', 'programa_sede.id_programa_sede')
            ->join('programa', 'programa_sede.id_programa', '=', 'programa.id_programa')
            ->whereIn('rol.nombre', ['Estudiante', 'Graduado'])
            ->select('rol.nombre as rol', 'programa.id_facultad')
            ->distinct()->get()
            ->groupBy('rol')
            ->map(fn($rows) => $rows->pluck('id_facultad')->values());

        $hayFiltros = $f['busqueda'] !== '' || collect($f)->except('busqueda')->flatten()->filter()->isNotEmpty()
                   || $this->hasBeneficiaryFilters($b);

        $hojasDisponibles   = $this->computeHojasDisponibles($b);
        $hojasSeleccionadas = array_values(array_intersect(
            $this->parseArrayInput($request, 'hojas'),
            $hojasDisponibles,
        ));
        if (empty($hojasSeleccionadas)) {
            $hojasSeleccionadas = $hojasDisponibles;
        }

        return view('servicios.reportes', array_merge($f, $b, $dd, compact(
            'totalServicios', 'totalAsignaciones', 'hayFiltros',
            'facultades', 'tiposEmpleadoList', 'dependencias', 'cargos',
            'programasDisponibles', 'planesDisponibles',
            'hojasDisponibles', 'hojasSeleccionadas',
            'nombresServiciosDisponibles',
            'tipoDependenciaMap', 'tipoCargoMap', 'rolFacultadMap'
        )));
    }

    public function programasPorFacultad(Request $request)
    {
        $idFacultades = $this->parseArrayInput($request, 'id_facultad');

        $programas = Programa::when(!empty($idFacultades), fn($q) => $q->whereIn('id_facultad', $idFacultades))
            ->orderBy('nombre')
            ->get(['id_programa as id', 'nombre']);

        return response()->json($programas);
    }

    public function planesPorPrograma(Request $request)
    {
        $idProgramas = $this->parseArrayInput($request, 'id_programa');

        $planes = PlanEstudio::when(!empty($idProgramas), fn($q) =>
            $q->whereHas('programaSede', fn($ps) => $ps->whereIn('id_programa', $idProgramas))
        )
        ->orderBy('codigo_plan')
        ->get()
        ->map(fn($p) => ['id' => $p->id_plan_estudio, 'text' => 'Plan ' . $p->codigo_plan]);

        return response()->json($planes);
    }

    public function descargar(Request $request)
    {
        $f = $this->parseServiceFilters($request);
        $b = $this->parseBeneficiaryFilters($request);

        $hojasDisponibles   = $this->computeHojasDisponibles($b);
        $hojasSeleccionadas = array_values(array_intersect(
            $this->parseArrayInput($request, 'hojas'),
            $hojasDisponibles,
        ));
        $hojas = empty($hojasSeleccionadas) ? $hojasDisponibles : $hojasSeleccionadas;

        $query     = Servicio::query();
        $hayBenef  = $this->hasBeneficiaryFilters($b);
        $benFilter = $this->buildBeneficiaryFilter($b);

        $this->applyServiceFilters($query, $f);

        if ($hayBenef) {
            $query->whereHas('usuariosAsignados', $benFilter);
        }

        $query->with([
            'linea.componente.area', 'tipoActividad', 'sede', 'periodo',
            'usuariosAsignados' => function ($q) use ($hayBenef, $benFilter) {
                if ($hayBenef) $benFilter($q);
                $q->with([
                    'usuario', 'rol',
                    'estudianteEgresado.planEstudio.programaSede.programa.facultad',
                    'empleado.tipoEmpleado', 'empleado.dependencia', 'empleado.cargo',
                ]);
            },
        ]);

        $servicios = $query->orderBy('id_periodo')->orderByDesc('fecha')->get();

        $export      = new \App\Exports\ServicioExport($servicios, $hojas);
        $spreadsheet = $export->build();
        $filename    = 'reporte_servicios_' . now()->format('Y-m-d_H-i') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
