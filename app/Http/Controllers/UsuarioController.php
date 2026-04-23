<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use App\Models\Rol;
use App\Models\Sede;
use App\Models\Usuario;
use App\Models\UsuarioRolSede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $busqueda        = trim($request->get('q', ''));
        $idSede          = $request->get('id_sede');
        $idRol           = $request->get('id_rol');
        $idPeriodo       = $request->get('id_periodo');
        $estado          = $request->get('estado');

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

        if ($idSede) {
            $query->whereHas('rolesEnSedes', fn($q) => $q->where('id_sede', $idSede));
        }

        if ($idRol) {
            $query->whereHas('rolesEnSedes', fn($q) => $q->where('id_rol', $idRol));
        }

        if ($idPeriodo) {
            $query->whereHas('rolesEnSedes', fn($q) => $q->where('id_periodo', $idPeriodo));
        }

        if ($estado) {
            $query->whereHas('rolesEnSedes', fn($q) => $q->where('estado', $estado));
        }

        $usuarios = $query
            ->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->paginate(30)
            ->withQueryString();

        $sedes   = Sede::orderBy('nombre')->get();
        $roles   = Rol::orderBy('nombre')->get();
        $periodos = Periodo::orderByDesc('nombre')->get();

        return view('usuarios.index', compact(
            'usuarios', 'busqueda',
            'sedes', 'roles', 'periodos',
            'idSede', 'idRol', 'idPeriodo', 'estado'
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
