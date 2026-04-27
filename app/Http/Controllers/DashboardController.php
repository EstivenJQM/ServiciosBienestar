<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $periodos = Periodo::orderByDesc('nombre')->get();
        $sedes    = Sede::orderBy('nombre')->get();

        $idPeriodo = $request->input('id_periodo') ?: optional($periodos->first())->id_periodo;
        $idSede    = $request->input('id_sede');

        $periodoActual = $periodos->firstWhere('id_periodo', $idPeriodo);

        // Closures que devuelven un QueryBuilder fresco cada vez
        $baseS = fn() => DB::table('servicio as s')
            ->where('s.id_periodo', $idPeriodo)
            ->when($idSede, fn($q) => $q->where('s.id_sede', $idSede));

        $baseSU = fn() => DB::table('servicio_usuario as su')
            ->join('servicio as s', 'su.id_servicio', '=', 's.id_servicio')
            ->join('usuario_rol_sede as urs', 'su.id_usuario_rol_sede', '=', 'urs.id_usuario_rol_sede')
            ->where('s.id_periodo', $idPeriodo)
            ->when($idSede, fn($q) => $q->where('s.id_sede', $idSede));

        // ── KPIs ──────────────────────────────────────────────────────
        $totalServicios    = $baseS()->count();
        $totalAsignaciones = $baseSU()->count();
        $totalPersonas     = $baseSU()->distinct('urs.id_usuario')->count('urs.id_usuario');
        $promedioBenef     = $totalServicios > 0 ? round($totalAsignaciones / $totalServicios, 1) : 0;

        // ── Por rol ───────────────────────────────────────────────────
        $porRol = $baseSU()
            ->join('rol as r', 'urs.id_rol', '=', 'r.id_rol')
            ->select('r.nombre', DB::raw('COUNT(DISTINCT urs.id_usuario) as total'))
            ->groupBy('r.id_rol', 'r.nombre')
            ->orderByDesc('total')
            ->get();

        // ── Por sede ──────────────────────────────────────────────────
        $porSede = $baseSU()
            ->join('sede as se', 's.id_sede', '=', 'se.id_sede')
            ->select('se.nombre', DB::raw('COUNT(DISTINCT urs.id_usuario) as total'))
            ->groupBy('se.id_sede', 'se.nombre')
            ->orderByDesc('total')
            ->get();

        // ── Por área ──────────────────────────────────────────────────
        $porArea = $baseS()
            ->join('linea as l', 's.id_linea', '=', 'l.id_linea')
            ->join('componente as c', 'l.id_componente', '=', 'c.id_componente')
            ->join('area as a', 'c.id_area', '=', 'a.id_area')
            ->select('a.nombre', DB::raw('COUNT(s.id_servicio) as total'))
            ->groupBy('a.id_area', 'a.nombre')
            ->orderByDesc('total')
            ->get();

        // ── Por tipo actividad ────────────────────────────────────────
        $porTipoActividad = $baseS()
            ->join('tipo_actividad as ta', 's.id_tipo_actividad', '=', 'ta.id_tipo_actividad')
            ->select('ta.nombre', DB::raw('COUNT(s.id_servicio) as total'))
            ->groupBy('ta.id_tipo_actividad', 'ta.nombre')
            ->orderByDesc('total')
            ->get();

        // ── Top 5 programas ───────────────────────────────────────────
        $topProgramas = $baseSU()
            ->join('estudiante_egresado as ee', 'urs.id_usuario_rol_sede', '=', 'ee.id_usuario_rol_sede')
            ->join('plan_estudio as pe', 'ee.id_plan_estudio', '=', 'pe.id_plan_estudio')
            ->join('programa_sede as ps', 'pe.id_programa_sede', '=', 'ps.id_programa_sede')
            ->join('programa as p', 'ps.id_programa', '=', 'p.id_programa')
            ->select('p.nombre', DB::raw('COUNT(DISTINCT urs.id_usuario) as total'))
            ->groupBy('p.id_programa', 'p.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ── Por tipo empleado ─────────────────────────────────────────
        $porTipoEmpleado = $baseSU()
            ->join('empleado as e', 'urs.id_usuario_rol_sede', '=', 'e.id_usuario_rol_sede')
            ->join('tipo_empleado as te', 'e.id_tipo_empleado', '=', 'te.id_tipo_empleado')
            ->select('te.nombre', DB::raw('COUNT(DISTINCT urs.id_usuario) as total'))
            ->groupBy('te.id_tipo_empleado', 'te.nombre')
            ->orderByDesc('total')
            ->get();

        // ── Top 5 dependencias ────────────────────────────────────────
        $topDependencias = $baseSU()
            ->join('empleado as e', 'urs.id_usuario_rol_sede', '=', 'e.id_usuario_rol_sede')
            ->join('dependencia as d', 'e.id_dependencia', '=', 'd.id_dependencia')
            ->select('d.nombre', DB::raw('COUNT(DISTINCT urs.id_usuario) as total'))
            ->groupBy('d.id_dependencia', 'd.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ── Tendencia por período (no filtra por período, solo sede) ──
        $tendencia = DB::table('periodo as p')
            ->leftJoin('servicio as s', function ($j) use ($idSede) {
                $j->on('p.id_periodo', '=', 's.id_periodo');
                if ($idSede) $j->where('s.id_sede', '=', $idSede);
            })
            ->leftJoin('servicio_usuario as su', 's.id_servicio', '=', 'su.id_servicio')
            ->select(
                'p.nombre as periodo',
                DB::raw('COUNT(DISTINCT s.id_servicio) as servicios'),
                DB::raw('COUNT(su.id_usuario_rol_sede) as asignaciones')
            )
            ->groupBy('p.id_periodo', 'p.nombre')
            ->orderBy('p.nombre')
            ->get();

        return view('dashboard', compact(
            'periodos', 'sedes', 'idPeriodo', 'idSede', 'periodoActual',
            'totalServicios', 'totalPersonas', 'totalAsignaciones', 'promedioBenef',
            'porRol', 'porSede', 'porArea', 'porTipoActividad',
            'topProgramas', 'porTipoEmpleado', 'topDependencias', 'tendencia'
        ));
    }
}
