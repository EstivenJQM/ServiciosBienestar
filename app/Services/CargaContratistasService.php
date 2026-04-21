<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CargaContratistasService
{
    private array $sedeCache       = [];
    private array $dependenciaCache = [];

    private int    $idRol;
    private int    $idTipoEmpleado;
    private int    $idPeriodo;

    private const NOMBRE_ROL = 'Contratista';

    public function cargar(UploadedFile $archivo, int $idPeriodo): array
    {
        set_time_limit(300);

        $this->idPeriodo = $idPeriodo;
        $this->idRol     = (int) DB::table('rol')->where('nombre', 'Empleado')->value('id_rol');
        $this->idTipoEmpleado = (int) DB::table('tipo_empleado')->where('nombre', 'Contratista')->value('id_tipo_empleado');

        if (! $this->idRol || ! $this->idTipoEmpleado) {
            return [
                'creados'      => 0,
                'actualizados' => 0,
                'errores'      => ['No se encontró el rol "Empleado" o el tipo "Contratista" en el sistema.'],
                'total'        => 0,
            ];
        }

        $this->cargarCaches();

        $handle = fopen($archivo->getRealPath(), 'r');

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $cabecera = fgets($handle);
        $sep = str_contains($cabecera, ';') ? ';' : ',';

        $creados      = 0;
        $actualizados = 0;
        $errores      = [];
        $fila         = 1;

        while (($cols = fgetcsv($handle, 0, $sep)) !== false) {
            $fila++;

            if (count($cols) < 6) {
                $errores[] = "Fila {$fila}: solo " . count($cols) . " columnas (se esperan al menos 6).";
                continue;
            }

            [
                $documento,
                $nombres,
                $apellidos,
                $correo,
                $nombreSede,
                $dependencia,
            ] = array_map('trim', array_slice($cols, 0, 6));

            $vacios = [];
            if (empty($documento))   $vacios[] = 'documento';
            if (empty($nombres))     $vacios[] = 'nombres';
            if (empty($apellidos))   $vacios[] = 'apellidos';
            if (empty($correo))      $vacios[] = 'correo';
            if (empty($nombreSede))  $vacios[] = 'nombre sede';
            if (empty($dependencia)) $vacios[] = 'dependencia';

            if (! empty($vacios)) {
                $msg = 'Fila ' . $fila . ': campo(s) obligatorio(s) vacío(s): ' . implode(', ', $vacios) . '.';
                $errores[] = $msg;
                $this->guardarInconsistencia(
                    $idPeriodo, $fila,
                    $documento, $nombres, $apellidos, $correo,
                    $nombreSede, $dependencia,
                    implode(', ', array_map(fn($c) => ucfirst($c) . ' es obligatorio', $vacios)) . '.'
                );
                continue;
            }

            try {
                DB::beginTransaction();

                $sede  = $this->resolverSede($nombreSede);
                $idDep = $this->resolverDependencia($dependencia);
                [$usuario, $esNuevo] = $this->resolverUsuario($documento, $nombres, $apellidos, $correo);
                $idUrs = $this->resolverUsuarioRolSede($usuario->id_usuario, $sede->id_sede);
                $this->resolverEmpleado($idUrs);
                $this->resolverEmpleadoContratista($idUrs, $idDep);

                DB::commit();
                $esNuevo ? $creados++ : $actualizados++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $msgError  = $e->getMessage();
                $errores[] = "Fila {$fila} (doc: {$documento}): {$msgError}";
                $this->guardarInconsistencia(
                    $idPeriodo, $fila,
                    $documento, $nombres, $apellidos, $correo,
                    $nombreSede, $dependencia, $msgError
                );
            }
        }

        fclose($handle);

        return [
            'creados'      => $creados,
            'actualizados' => $actualizados,
            'errores'      => $errores,
            'total'        => $fila - 1,
        ];
    }

    private function cargarCaches(): void
    {
        foreach (DB::table('sede')->get() as $s) {
            $this->sedeCache[$this->normalizar($s->nombre)] = $s;
        }

        foreach (DB::table('dependencia')->get() as $d) {
            $this->dependenciaCache[$this->normalizar($d->nombre)] = $d->id_dependencia;
        }

        foreach (DB::table('dependencia_alias')->get() as $a) {
            $this->dependenciaCache[$this->normalizar($a->alias)] = $a->id_dependencia;
        }
    }

    private function resolverSede(string $nombre): object
    {
        $norm = $this->normalizar($nombre);

        if (isset($this->sedeCache[$norm])) {
            return $this->sedeCache[$norm];
        }

        $claveFuzzy = $this->buscarFuzzy($norm, array_keys($this->sedeCache));
        if ($claveFuzzy !== null) {
            return $this->sedeCache[$claveFuzzy];
        }

        throw new \RuntimeException(
            "La sede \"{$nombre}\" no existe. Regístrela primero en el módulo de Sedes."
        );
    }

    private function resolverDependencia(string $nombre): int
    {
        $norm = $this->normalizar($nombre);

        if (isset($this->dependenciaCache[$norm])) {
            return $this->dependenciaCache[$norm];
        }

        $claveFuzzy = $this->buscarFuzzy($norm, array_keys($this->dependenciaCache));
        if ($claveFuzzy !== null) {
            return $this->dependenciaCache[$claveFuzzy];
        }

        $nombreLimpio = mb_strtoupper(trim($nombre));
        $id = DB::table('dependencia')->insertGetId([
            'nombre'     => $nombreLimpio,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->dependenciaCache[$norm] = $id;
        return $id;
    }

    private function resolverUsuario(
        string $documento,
        string $nombres,
        string $apellidos,
        string $correo
    ): array {
        [$primerNombre, $segundoNombre]    = $this->partirNombre($nombres);
        [$primerApellido, $segundoApellido] = $this->partirNombre($apellidos);

        $existente = DB::table('usuario')->where('documento', $documento)->first();

        $datos = [
            'primer_nombre'    => mb_strtoupper($primerNombre),
            'segundo_nombre'   => $segundoNombre ? mb_strtoupper($segundoNombre) : null,
            'primer_apellido'  => mb_strtoupper($primerApellido),
            'segundo_apellido' => $segundoApellido ? mb_strtoupper($segundoApellido) : null,
            'correo'           => mb_strtolower($correo),
            'updated_at'       => now(),
        ];

        if ($existente) {
            DB::table('usuario')->where('id_usuario', $existente->id_usuario)->update($datos);
            return [DB::table('usuario')->where('id_usuario', $existente->id_usuario)->first(), false];
        }

        $id = DB::table('usuario')->insertGetId(array_merge($datos, [
            'documento'  => $documento,
            'created_at' => now(),
        ]));

        return [DB::table('usuario')->where('id_usuario', $id)->first(), true];
    }

    private function resolverUsuarioRolSede(int $idUsuario, int $idSede): int
    {
        $existente = DB::table('usuario_rol_sede')
            ->where('id_usuario', $idUsuario)
            ->where('id_rol',     $this->idRol)
            ->where('id_sede',    $idSede)
            ->where('id_periodo', $this->idPeriodo)
            ->value('id_usuario_rol_sede');

        if ($existente) {
            return $existente;
        }

        return DB::table('usuario_rol_sede')->insertGetId([
            'id_usuario'  => $idUsuario,
            'id_rol'      => $this->idRol,
            'id_sede'     => $idSede,
            'id_periodo'  => $this->idPeriodo,
            'estado'      => 'activo',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    private function resolverEmpleado(int $idUrs): void
    {
        $existe = DB::table('empleado')->where('id_usuario_rol_sede', $idUrs)->exists();

        if (! $existe) {
            DB::table('empleado')->insert([
                'id_usuario_rol_sede' => $idUrs,
                'id_tipo_empleado'    => $this->idTipoEmpleado,
            ]);
        }
    }

    private function resolverEmpleadoContratista(int $idUrs, int $idDependencia): void
    {
        $existe = DB::table('empleado_contratista')->where('id_usuario_rol_sede', $idUrs)->exists();

        if ($existe) {
            DB::table('empleado_contratista')
                ->where('id_usuario_rol_sede', $idUrs)
                ->update(['id_dependencia' => $idDependencia]);
        } else {
            DB::table('empleado_contratista')->insert([
                'id_usuario_rol_sede' => $idUrs,
                'id_dependencia'      => $idDependencia,
                'id_cargo'            => null,
                'codigo_cargo'        => null,
            ]);
        }
    }

    public function procesarFilaIndividual(
        int    $idPeriodo,
        string $documento,
        string $nombres,
        string $apellidos,
        string $correo,
        string $nombreSede,
        string $dependencia,
    ): array {
        $this->idPeriodo      = $idPeriodo;
        $this->idRol          = (int) DB::table('rol')->where('nombre', 'Empleado')->value('id_rol');
        $this->idTipoEmpleado = (int) DB::table('tipo_empleado')->where('nombre', 'Contratista')->value('id_tipo_empleado');

        $this->sedeCache        = [];
        $this->dependenciaCache = [];
        $this->cargarCaches();

        $vacios = [];
        if (empty($documento))   $vacios[] = 'documento';
        if (empty($nombres))     $vacios[] = 'nombres';
        if (empty($apellidos))   $vacios[] = 'apellidos';
        if (empty($correo))      $vacios[] = 'correo';
        if (empty($nombreSede))  $vacios[] = 'nombre sede';
        if (empty($dependencia)) $vacios[] = 'dependencia';

        if (! empty($vacios)) {
            return [false, implode(', ', array_map(fn($c) => ucfirst($c) . ' es obligatorio', $vacios)) . '.'];
        }

        try {
            DB::beginTransaction();

            $sede  = $this->resolverSede($nombreSede);
            $idDep = $this->resolverDependencia($dependencia);
            [$usuario,] = $this->resolverUsuario($documento, $nombres, $apellidos, $correo);
            $idUrs = $this->resolverUsuarioRolSede($usuario->id_usuario, $sede->id_sede);
            $this->resolverEmpleado($idUrs);
            $this->resolverEmpleadoContratista($idUrs, $idDep);

            DB::commit();
            return [true, null];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [false, $e->getMessage()];
        }
    }

    private function guardarInconsistencia(
        int    $idPeriodo,
        int    $fila,
        string $documento,
        string $nombres,
        string $apellidos,
        string $correo,
        string $nombreSede,
        string $dependencia,
        string $error,
    ): void {
        try {
            DB::table('carga_inconsistencia')->insert([
                'id_periodo'   => $idPeriodo,
                'nombre_rol'   => self::NOMBRE_ROL,
                'fila'         => $fila,
                'documento'    => $documento,
                'nombres'      => $nombres,
                'apellidos'    => $apellidos,
                'email'        => $correo,
                'codigo_sede'  => '',
                'nombre_sede'  => $nombreSede,
                'dependencia'  => $dependencia,
                'codigo_plan'  => '',
                'nombre_programa' => '',
                'nombre_facultad' => '',
                'error'        => $error,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Throwable $ex) {
            Log::error('No se pudo guardar inconsistencia contratista: ' . $ex->getMessage());
        }
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtoupper(trim($texto));
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return preg_replace('/\s+/', ' ', $texto);
    }

    private function buscarFuzzy(string $entrada, array $opciones, float $umbral = 82.0): ?string
    {
        if (empty($opciones)) return null;

        $mejorPct   = 0.0;
        $mejorClave = null;

        foreach ($opciones as $candidato) {
            similar_text($entrada, $candidato, $pct);
            if ($pct > $mejorPct) {
                $mejorPct   = $pct;
                $mejorClave = $candidato;
            }
        }

        return $mejorPct >= $umbral ? $mejorClave : null;
    }

    private function partirNombre(string $texto): array
    {
        $partes = preg_split('/\s+/', trim($texto), 2);
        return [
            $partes[0] ?? '',
            isset($partes[1]) && $partes[1] !== '' ? $partes[1] : null,
        ];
    }
}
