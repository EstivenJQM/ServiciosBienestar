<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Componente;
use App\Models\Linea;
use App\Models\Periodo;
use App\Models\Sede;
use App\Models\Servicio;
use App\Models\TipoActividad;
use App\Services\CargaServicioUsuariosService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $busqueda        = trim($request->input('q', ''));
        $idPeriodo       = $request->input('id_periodo');
        $idSede          = $request->input('id_sede');
        $idArea          = $request->input('id_area');
        $idComponente    = $request->input('id_componente');
        $idLinea         = $request->input('id_linea');
        $idTipoActividad = $request->input('id_tipo_actividad');
        $fechaDesde      = $request->input('fecha_desde');
        $fechaHasta      = $request->input('fecha_hasta');

        $query = Servicio::with([
            'linea.componente.area',
            'tipoActividad',
            'sede',
            'periodo',
        ])->withCount('usuariosAsignados');

        if ($busqueda !== '') {
            $query->where('nombre', 'like', "%{$busqueda}%");
        }
        if ($idPeriodo) {
            $query->where('id_periodo', $idPeriodo);
        }
        if ($idSede) {
            $query->where('id_sede', $idSede);
        }
        if ($idArea) {
            $query->whereHas('linea.componente', fn($q) => $q->where('id_area', $idArea));
        }
        if ($idComponente) {
            $query->whereHas('linea', fn($q) => $q->where('id_componente', $idComponente));
        }
        if ($idLinea) {
            $query->where('id_linea', $idLinea);
        }
        if ($idTipoActividad) {
            $query->where('id_tipo_actividad', $idTipoActividad);
        }
        if ($fechaDesde) {
            $query->where('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->where('fecha', '<=', $fechaHasta);
        }

        $coleccion = $query->orderByDesc('fecha')->get();
        $total     = $coleccion->count();
        $servicios = $coleccion->groupBy('id_periodo');

        $periodos       = Periodo::orderByDesc('nombre')->get()->keyBy('id_periodo');
        $sedes          = Sede::orderBy('nombre')->get();
        $areas          = Area::orderBy('nombre')->get();
        $componentes    = Componente::orderBy('nombre')->get();
        $lineas         = Linea::orderBy('nombre')->get();
        $tiposActividad = TipoActividad::orderBy('nombre')->get();

        $hayFiltros = $busqueda !== '' || $idPeriodo || $idSede || $idArea
                    || $idComponente || $idLinea || $idTipoActividad
                    || $fechaDesde || $fechaHasta;

        return view('servicios.index', compact(
            'servicios', 'periodos', 'total', 'hayFiltros',
            'busqueda', 'idPeriodo', 'idSede', 'idArea', 'idComponente',
            'idLinea', 'idTipoActividad', 'fechaDesde', 'fechaHasta',
            'sedes', 'areas', 'componentes', 'lineas', 'tiposActividad'
        ));
    }

    public function show(Servicio $servicio)
    {
        $servicio->load([
            'linea.componente.area',
            'tipoActividad',
            'sede',
            'periodo',
            'usuariosAsignados.usuario',
            'usuariosAsignados.rol',
        ]);

        return view('servicios.show', compact('servicio'));
    }

    public function create()
    {
        $lineas   = Linea::with(['componente.area', 'tiposActividad'])
            ->orderBy('id_componente')
            ->orderBy('nombre')
            ->get();
        $sedes    = Sede::orderBy('nombre')->get();
        $periodos = Periodo::orderByDesc('nombre')->get();

        return view('servicios.create', compact('lineas', 'sedes', 'periodos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'           => 'required|string|max:200',
            'id_linea'         => 'required|exists:linea,id_linea',
            'id_tipo_actividad'=> 'required|exists:tipo_actividad,id_tipo_actividad',
            'id_sede'          => 'required|exists:sede,id_sede',
            'fecha'            => 'required|date',
            'id_periodo'       => 'required|exists:periodo,id_periodo',
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

        // Verificar que la combinación línea + tipo actividad existe en linea_tipo_actividad
        $combinacionValida = \DB::table('linea_tipo_actividad')
            ->where('id_linea', $request->id_linea)
            ->where('id_tipo_actividad', $request->id_tipo_actividad)
            ->exists();

        if (! $combinacionValida) {
            return back()->withInput()
                ->withErrors(['id_tipo_actividad' => 'El tipo de actividad no está asociado a la línea seleccionada.']);
        }

        Servicio::create([
            'nombre'            => $request->nombre,
            'id_linea'          => $request->id_linea,
            'id_tipo_actividad' => $request->id_tipo_actividad,
            'id_sede'           => $request->id_sede,
            'fecha'             => $request->fecha,
            'id_periodo'        => $request->id_periodo,
        ]);

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio)
    {
        $lineas   = Linea::with(['componente.area', 'tiposActividad'])
            ->orderBy('id_componente')
            ->orderBy('nombre')
            ->get();
        $sedes    = Sede::orderBy('nombre')->get();
        $periodos = Periodo::orderByDesc('nombre')->get();

        return view('servicios.edit', compact('servicio', 'lineas', 'sedes', 'periodos'));
    }

    public function update(Request $request, Servicio $servicio)
    {
        $request->validate([
            'nombre'           => 'required|string|max:200',
            'id_linea'         => 'required|exists:linea,id_linea',
            'id_tipo_actividad'=> 'required|exists:tipo_actividad,id_tipo_actividad',
            'id_sede'          => 'required|exists:sede,id_sede',
            'fecha'            => 'required|date',
            'id_periodo'       => 'required|exists:periodo,id_periodo',
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

        $combinacionValida = \DB::table('linea_tipo_actividad')
            ->where('id_linea', $request->id_linea)
            ->where('id_tipo_actividad', $request->id_tipo_actividad)
            ->exists();

        if (! $combinacionValida) {
            return back()->withInput()
                ->withErrors(['id_tipo_actividad' => 'El tipo de actividad no está asociado a la línea seleccionada.']);
        }

        $servicio->update([
            'nombre'            => $request->nombre,
            'id_linea'          => $request->id_linea,
            'id_tipo_actividad' => $request->id_tipo_actividad,
            'id_sede'           => $request->id_sede,
            'fecha'             => $request->fecha,
            'id_periodo'        => $request->id_periodo,
        ]);

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio eliminado correctamente.');
    }

    public function asignarUsuarios(
        Request $request,
        Servicio $servicio,
        CargaServicioUsuariosService $service
    ) {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'archivo.required' => 'Seleccione un archivo CSV.',
            'archivo.mimes'    => 'El archivo debe ser .csv.',
            'archivo.max'      => 'El archivo no debe superar 5 MB.',
        ]);

        $resultado = $service->asignar($request->file('archivo'), $servicio);

        return redirect()->route('servicios.show', $servicio)
            ->with('resultado_asignacion', $resultado);
    }

    public function desasignarUsuario(Servicio $servicio, int $idUrs)
    {
        \DB::table('servicio_usuario')
            ->where('id_servicio',         $servicio->id_servicio)
            ->where('id_usuario_rol_sede',  $idUrs)
            ->delete();

        return back()->with('success', 'Usuario desvinculado del servicio.');
    }
}
