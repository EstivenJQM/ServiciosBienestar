<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use App\Models\NivelAcademico;
use App\Models\Programa;
use App\Models\ProgramaSede;
use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramaController extends Controller
{
    public function index()
    {
        $programas = Programa::with([
            'facultad',
            'tipoFormacion.nivel',
            'sedes' => fn($q) => $q->orderBy('nombre'),
            'programaSedes.planesEstudio',
        ])
            ->orderBy('id_facultad')
            ->orderBy('nombre')
            ->get()
            ->groupBy('id_facultad');

        $facultades = Facultad::orderBy('nombre')->get()->keyBy('id_facultad');

        return view('programas.index', compact('programas', 'facultades'));
    }

    public function create()
    {
        $facultades   = Facultad::orderBy('nombre')->get();
        $niveles      = NivelAcademico::with(['tiposFormacion' => fn($q) => $q->orderBy('nombre')])->get();
        $sedes        = Sede::orderBy('nombre')->get();

        return view('programas.create', compact('facultades', 'niveles', 'sedes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_facultad'       => 'required|exists:facultad,id_facultad',
            'id_tipo_formacion' => 'nullable|exists:tipo_formacion,id_tipo_formacion',
            'nombre'            => 'required|string|max:150',
            'sedes'             => 'required|array|min:1',
            'sedes.*'           => 'exists:sede,id_sede',
            'codigo_snies'      => 'nullable|array',
            'codigo_snies.*'    => 'nullable|string|max:20',
            'planes_estudio'    => 'nullable|array',
            'planes_estudio.*'  => 'nullable|string|max:200',
        ], [
            'id_facultad.required' => 'Seleccione una facultad.',
            'id_facultad.exists'   => 'La facultad seleccionada no existe.',
            'nombre.required'      => 'El nombre del programa es obligatorio.',
            'nombre.max'           => 'El nombre no puede superar los 150 caracteres.',
            'sedes.required'       => 'Debe asociar al menos una sede.',
            'sedes.min'            => 'Debe asociar al menos una sede.',
        ]);

        $programa = Programa::create([
            'id_facultad'       => $request->id_facultad,
            'id_tipo_formacion' => $request->id_tipo_formacion,
            'nombre'            => $request->nombre,
        ]);

        $programa->sedes()->sync($this->buildSedesSync($request));
        $this->syncPlanesEstudio($programa, $request);

        return redirect()->route('programas.index')
            ->with('success', 'Programa creado correctamente.');
    }

    public function edit(Programa $programa)
    {
        $facultades = Facultad::orderBy('nombre')->get();
        $niveles    = NivelAcademico::with(['tiposFormacion' => fn($q) => $q->orderBy('nombre')])->get();
        $sedes      = Sede::orderBy('nombre')->get();

        $sedesPrograma = $programa->sedes->keyBy('id_sede');

        $planesEstudioBySede = ProgramaSede::where('id_programa', $programa->id_programa)
            ->with('planesEstudio')
            ->get()
            ->keyBy('id_sede')
            ->map(fn($ps) => $ps->planesEstudio->pluck('codigo_plan')->implode(', '));

        return view('programas.edit', compact(
            'programa', 'facultades', 'niveles', 'sedes', 'sedesPrograma', 'planesEstudioBySede'
        ));
    }

    public function update(Request $request, Programa $programa)
    {
        $request->validate([
            'id_facultad'       => 'required|exists:facultad,id_facultad',
            'id_tipo_formacion' => 'nullable|exists:tipo_formacion,id_tipo_formacion',
            'nombre'            => 'required|string|max:150',
            'sedes'             => 'required|array|min:1',
            'sedes.*'           => 'exists:sede,id_sede',
            'codigo_snies'      => 'nullable|array',
            'codigo_snies.*'    => 'nullable|string|max:20',
            'planes_estudio'    => 'nullable|array',
            'planes_estudio.*'  => 'nullable|string|max:200',
        ], [
            'id_facultad.required' => 'Seleccione una facultad.',
            'id_facultad.exists'   => 'La facultad seleccionada no existe.',
            'nombre.required'      => 'El nombre del programa es obligatorio.',
            'nombre.max'           => 'El nombre no puede superar los 150 caracteres.',
            'sedes.required'       => 'Debe asociar al menos una sede.',
            'sedes.min'            => 'Debe asociar al menos una sede.',
        ]);

        // Eliminar planes de estudio de sedes que se van a desasociar (evita FK constraint)
        $removedSedeIds = array_diff(
            $programa->sedes->pluck('id_sede')->toArray(),
            $request->sedes
        );
        if ($removedSedeIds) {
            ProgramaSede::where('id_programa', $programa->id_programa)
                ->whereIn('id_sede', $removedSedeIds)
                ->get()
                ->each(fn($ps) => $ps->planesEstudio()->delete());
        }

        $programa->update([
            'id_facultad'       => $request->id_facultad,
            'id_tipo_formacion' => $request->id_tipo_formacion,
            'nombre'            => $request->nombre,
        ]);

        $programa->sedes()->sync($this->buildSedesSync($request));
        $this->syncPlanesEstudio($programa, $request);

        return redirect()->route('programas.index')
            ->with('success', 'Programa actualizado correctamente.');
    }

    public function asignacionSnies()
    {
        $programas = Programa::with([
            'facultad',
            'tipoFormacion',
            'sedes' => fn($q) => $q->orderBy('nombre'),
        ])
            ->orderBy('id_facultad')
            ->orderBy('nombre')
            ->get()
            ->groupBy('id_facultad');

        $facultades = Facultad::orderBy('nombre')->get()->keyBy('id_facultad');
        $niveles    = NivelAcademico::with(['tiposFormacion' => fn($q) => $q->orderBy('nombre')])->get();

        return view('programas.asignacion-snies', compact('programas', 'facultades', 'niveles'));
    }

    public function guardarAsignacionSnies(Request $request)
    {
        $request->validate([
            'tipo_formacion'     => 'nullable|array',
            'tipo_formacion.*'   => 'nullable|exists:tipo_formacion,id_tipo_formacion',
            'codigo_snies'       => 'nullable|array',
            'codigo_snies.*'     => 'nullable|array',
            'codigo_snies.*.*'   => 'nullable|string|max:20',
        ]);

        foreach ($request->tipo_formacion ?? [] as $idPrograma => $idTipoFormacion) {
            Programa::where('id_programa', $idPrograma)
                ->update(['id_tipo_formacion' => $idTipoFormacion ?: null]);
        }

        foreach ($request->codigo_snies ?? [] as $idPrograma => $sedes) {
            foreach ($sedes as $idSede => $codigoSnies) {
                ProgramaSede::where('id_programa', $idPrograma)
                    ->where('id_sede', $idSede)
                    ->update(['codigo_snies' => $codigoSnies ?: null]);
            }
        }

        return redirect()->route('programas.asignacion-snies')
            ->with('success', 'Asignaciones guardadas correctamente.');
    }

    public function destroy(Programa $programa)
    {
        $programa->load('programaSedes');
        $programa->programaSedes->each(fn($ps) => $ps->planesEstudio()->delete());
        $programa->sedes()->detach();
        $programa->delete();

        return redirect()->route('programas.index')
            ->with('success', 'Programa eliminado correctamente.');
    }

    /** Construye el array para sync() con el pivot codigo_snies */
    private function buildSedesSync(Request $request): array
    {
        $sync = [];
        foreach ($request->sedes as $idSede) {
            $sync[$idSede] = [
                'codigo_snies' => $request->codigo_snies[$idSede] ?? null,
            ];
        }
        return $sync;
    }

    /** Sincroniza los planes de estudio para cada sede del programa */
    private function syncPlanesEstudio(Programa $programa, Request $request): void
    {
        foreach ($request->sedes as $idSede) {
            $ps = ProgramaSede::where('id_programa', $programa->id_programa)
                ->where('id_sede', $idSede)
                ->first();

            if (! $ps) {
                continue;
            }

            $raw    = $request->planes_estudio[$idSede] ?? '';
            $nuevos = array_values(array_filter(array_map('trim', explode(',', $raw))));

            $existentes = $ps->planesEstudio()->get();

            // Borrar solo los planes que ya no están en la lista Y no tienen estudiantes referenciados
            foreach ($existentes as $plan) {
                if (! in_array($plan->codigo_plan, $nuevos)) {
                    $tieneEstudiantes = \DB::table('estudiante_egresado')
                        ->where('id_plan_estudio', $plan->id_plan_estudio)
                        ->exists();

                    if (! $tieneEstudiantes) {
                        $plan->delete();
                    }
                }
            }

            // Agregar solo los planes que aún no existen
            $codigosActuales = $ps->planesEstudio()->pluck('codigo_plan')->toArray();
            foreach ($nuevos as $codigo) {
                if (! in_array($codigo, $codigosActuales)) {
                    $ps->planesEstudio()->create(['codigo_plan' => $codigo]);
                }
            }
        }
    }
}
