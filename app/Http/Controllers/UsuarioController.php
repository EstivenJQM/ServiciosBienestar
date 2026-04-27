<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Dependencia;
use App\Models\Facultad;
use App\Models\Periodo;
use App\Models\Programa;
use App\Models\Rol;
use App\Models\Sede;
use App\Models\TipoEmpleado;
use App\Models\Usuario;
use App\Models\UsuarioRolSede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    private function parseArr(Request $request, string $key): array
    {
        return array_values(array_filter((array) $request->input($key, [])));
    }

    public function index(Request $request)
    {
        $busqueda        = trim($request->get('q', ''));
        $idSedes         = $this->parseArr($request, 'id_sede');
        $idRoles         = $this->parseArr($request, 'id_rol');
        $idPeriodos      = $this->parseArr($request, 'id_periodo');
        $estado          = $request->get('estado');
        $idTiposEmpleado = $this->parseArr($request, 'id_tipo_empleado');
        $idDependencias  = $this->parseArr($request, 'id_dependencia');
        $idCargos        = $this->parseArr($request, 'id_cargo');
        $idFacultades    = $this->parseArr($request, 'id_facultad');
        $idProgramas     = $this->parseArr($request, 'id_programa');

        $query = Usuario::with([
            'rolesEnSedes.rol',
            'rolesEnSedes.sede',
            'rolesEnSedes.periodo',
            'rolesEnSedes.estudianteEgresado.planEstudio.programaSede.programa.facultad',
            'rolesEnSedes.empleado.tipoEmpleado',
            'rolesEnSedes.empleado.dependencia',
            'rolesEnSedes.empleado.cargo',
        ]);

        if ($busqueda !== '') {
            $query->where(fn($q) => $q
                ->where('documento',        'like', "%{$busqueda}%")
                ->orWhere('primer_nombre',   'like', "%{$busqueda}%")
                ->orWhere('segundo_nombre',  'like', "%{$busqueda}%")
                ->orWhere('primer_apellido', 'like', "%{$busqueda}%")
                ->orWhere('segundo_apellido','like', "%{$busqueda}%")
                ->orWhere('correo',          'like', "%{$busqueda}%")
            );
        }

        if (!empty($idSedes))         $query->whereHas('rolesEnSedes', fn($q) => $q->whereIn('id_sede', $idSedes));
        if (!empty($idRoles))         $query->whereHas('rolesEnSedes', fn($q) => $q->whereIn('id_rol', $idRoles));
        if (!empty($idPeriodos))      $query->whereHas('rolesEnSedes', fn($q) => $q->whereIn('id_periodo', $idPeriodos));
        if ($estado)                  $query->whereHas('rolesEnSedes', fn($q) => $q->where('estado', $estado));
        if (!empty($idTiposEmpleado)) $query->whereHas('rolesEnSedes.empleado', fn($q) => $q->whereIn('id_tipo_empleado', $idTiposEmpleado));
        if (!empty($idDependencias))  $query->whereHas('rolesEnSedes.empleado', fn($q) => $q->whereIn('id_dependencia', $idDependencias));
        if (!empty($idCargos))        $query->whereHas('rolesEnSedes.empleado', fn($q) => $q->whereIn('id_cargo', $idCargos));
        if (!empty($idFacultades))    $query->whereHas('rolesEnSedes.estudianteEgresado.planEstudio.programaSede.programa', fn($q) => $q->whereIn('id_facultad', $idFacultades));
        if (!empty($idProgramas))     $query->whereHas('rolesEnSedes.estudianteEgresado.planEstudio.programaSede', fn($q) => $q->whereIn('id_programa', $idProgramas));

        $usuarios = $query
            ->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->paginate(30)
            ->withQueryString();

        $sedes         = Sede::orderBy('nombre')->get();
        $roles         = Rol::orderBy('nombre')->get();
        $periodos      = Periodo::orderByDesc('nombre')->get();
        $tiposEmpleado = TipoEmpleado::orderBy('nombre')->get();
        $dependencias  = Dependencia::orderBy('nombre')->get();
        $cargos        = Cargo::orderBy('nombre')->get();
        $facultades    = Facultad::orderBy('nombre')->get();
        $programas     = Programa::orderBy('nombre')->get();

        return view('usuarios.index', compact(
            'usuarios', 'busqueda',
            'sedes', 'roles', 'periodos', 'tiposEmpleado', 'dependencias', 'cargos', 'facultades', 'programas',
            'idSedes', 'idRoles', 'idPeriodos', 'estado',
            'idTiposEmpleado', 'idDependencias', 'idCargos', 'idFacultades', 'idProgramas'
        ));
    }

    public function edit(Usuario $usuario)
    {
        $usuario->load(['rolesEnSedes.rol', 'rolesEnSedes.sede', 'rolesEnSedes.periodo']);
        $roles   = Rol::orderBy('nombre')->get();
        $sedes   = Sede::orderBy('nombre')->get();
        $periodos = Periodo::orderBy('nombre', 'desc')->get();

        return view('usuarios.edit', compact('usuario', 'roles', 'sedes', 'periodos'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $request->validate([
            'documento'        => 'required|string|max:20|unique:usuario,documento,' . $usuario->id_usuario . ',id_usuario',
            'primer_nombre'    => 'required|string|max:50',
            'segundo_nombre'   => 'nullable|string|max:50',
            'primer_apellido'  => 'required|string|max:50',
            'segundo_apellido' => 'nullable|string|max:50',
            'correo'           => 'nullable|email|max:100',
            // roles en sedes
            'roles.*.id_rol'     => 'required|exists:rol,id_rol',
            'roles.*.id_sede'    => 'required|exists:sede,id_sede',
            'roles.*.id_periodo' => 'nullable|exists:periodo,id_periodo',
            'roles.*.estado'     => 'required|in:activo,inactivo',
        ], [
            'documento.required'       => 'El documento es obligatorio.',
            'documento.unique'         => 'Ya existe un usuario con ese documento.',
            'primer_nombre.required'   => 'El primer nombre es obligatorio.',
            'primer_apellido.required' => 'El primer apellido es obligatorio.',
            'correo.email'             => 'El correo no tiene un formato válido.',
        ]);

        $usuario->update($request->only([
            'documento', 'primer_nombre', 'segundo_nombre',
            'primer_apellido', 'segundo_apellido', 'correo',
        ]));

        // Actualizar estado de cada rol en sede existente
        foreach ($request->roles ?? [] as $idUrs => $datos) {
            UsuarioRolSede::where('id_usuario_rol_sede', $idUrs)
                ->where('id_usuario', $usuario->id_usuario)
                ->update(['estado' => $datos['estado']]);
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $usuario)
    {
        DB::transaction(function () use ($usuario) {
            foreach ($usuario->rolesEnSedes as $urs) {
                $urs->estudianteEgresado?->delete();
                $urs->empleado?->delete();
            }
            $usuario->rolesEnSedes()->delete();
            $usuario->delete();
        });

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function destroyRol(Usuario $usuario, UsuarioRolSede $rolSede)
    {
        abort_if($rolSede->id_usuario !== $usuario->id_usuario, 403);

        $rolSede->estudianteEgresado?->delete();
        $rolSede->empleado?->delete();
        $rolSede->delete();

        return back()->with('success', 'Rol eliminado correctamente.');
    }
}
