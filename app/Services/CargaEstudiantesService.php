<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CargaEstudiantesService
{
    private array $sedeCache     = [];
    private array $facultadCache = [];
    private array $programaCache = [];
    private array $progSedeCache = [];
    private array $planCache     = [];

    private int    $idRol;
    private string $nombreRol;
    private int    $idPeriodo;

    // ─────────────────────────────────────────────
    //  Punto de entrada
    // ─────────────────────────────────────────────
    public function cargar(UploadedFile $archivo, int $idPeriodo, string $nombreRol = 'Estudiante'): array
    {
        set_time_limit(300);

        $this->idPeriodo = $idPeriodo;
        $this->nombreRol = $nombreRol;
        $this->idRol     = (int) DB::table('rol')->where('nombre', $nombreRol)->value('id_rol');

        if (! $this->idRol) {
            return [
                'creados'      => 0,
                'actualizados' => 0,
                'errores'      => ["El rol \"{$nombreRol}\" no existe en el sistema."],
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

            if (count($cols) < 9) {
                $errores[] = "Fila {$fila}: solo " . count($cols) . " columnas (se esperan 9).";
                continue;
            }

            [
                $documento,
                $nombres,
                $apellidos,
                $email,
                $codigoSede,
                $nombreSede,
                $codigoPlan,
                $nombrePrograma,
                $nombreFacultad,
            ] = array_map('trim', array_slice($cols, 0, 9));

            $camposVacios = [];
            if (empty($documento))      $camposVacios[] = 'documento';
            if (empty($nombres))        $camposVacios[] = 'nombres';
            if (empty($apellidos))      $camposVacios[] = 'apellidos';
            if (empty($email))          $camposVacios[] = 'email';
            if (empty($codigoSede))     $camposVacios[] = 'código de sede';
            if (empty($nombreSede))     $camposVacios[] = 'nombre de sede';
            if (empty($codigoPlan))     $camposVacios[] = 'código de plan';
            if (empty($nombrePrograma)) $camposVacios[] = 'nombre del programa';
            if (empty($nombreFacultad)) $camposVacios[] = 'nombre de la facultad';

            if (! empty($camposVacios)) {
                $msg       = 'Fila ' . $fila . ': campo(s) obligatorio(s) vacío(s): ' . implode(', ', $camposVacios) . '.';
                $errores[] = $msg;
                $this->guardarInconsistencia(
                    $idPeriodo, $nombreRol, $fila,
                    $documento, $nombres, $apellidos, $email,
                    $codigoSede, $nombreSede, $codigoPlan,
                    $nombrePrograma, $nombreFacultad,
                    implode(', ', array_map(fn($c) => ucfirst($c) . ' es obligatorio', $camposVacios)) . '.'
                );
                continue;
            }

            try {
                DB::beginTransaction();

                $sede       = $this->resolverSede($codigoSede, $nombreSede);
                $facultad   = $this->resolverFacultad($nombreFacultad);
                $this->resolverFacultadSede($facultad->id_facultad, $sede->id_sede);
                $programa   = $this->resolverPrograma($nombrePrograma, $facultad->id_facultad);
                $idProgSede = $this->resolverProgramaSede($programa->id_programa, $sede->id_sede);
                $idPlan     = $this->resolverPlanEstudio($idProgSede, $codigoPlan);
                [$usuario, $esNuevo] = $this->resolverUsuario($documento, $nombres, $apellidos, $email);
                $idUrs      = $this->resolverUsuarioRolSede($usuario->id_usuario, $sede->id_sede);
                $this->resolverEstudianteEgresado($idUrs, $idPlan);

                DB::commit();
                $esNuevo ? $creados++ : $actualizados++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $msgError  = $e->getMessage();
                $errores[] = "Fila {$fila} (doc: {$documento}): {$msgError}";

                $this->guardarInconsistencia(
                    $idPeriodo, $nombreRol, $fila,
                    $documento, $nombres, $apellidos, $email,
                    $codigoSede, $nombreSede, $codigoPlan,
                    $nombrePrograma, $nombreFacultad,
                    $msgError
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

    // ─────────────────────────────────────────────
    //  Carga inicial de caches
    // ─────────────────────────────────────────────
    private function cargarCaches(): void
    {
        foreach (DB::table('sede')->get() as $s) {
            $this->sedeCache[(string) $s->codigo] = $s;
        }

        foreach (DB::table('facultad')->get() as $f) {
            $this->facultadCache[$this->normalizar($f->nombre)] = $f;
        }

        foreach (DB::table('programa')->get() as $p) {
            $clave = $p->id_facultad . '|' . $this->normalizar($p->nombre);
            $this->programaCache[$clave] = $p;
        }

        foreach (DB::table('programa_sede')->get() as $ps) {
            $clave = $ps->id_programa . '|' . $ps->id_sede;
            $this->progSedeCache[$clave] = $ps->id_programa_sede;
        }

        foreach (DB::table('plan_estudio')->get() as $pl) {
            $clave = $pl->id_programa_sede . '|' . $pl->codigo_plan;
            $this->planCache[$clave] = $pl->id_plan_estudio;
        }
    }

    // ─────────────────────────────────────────────
    //  Resolución de cada entidad
    // ─────────────────────────────────────────────

    private function resolverSede(string $codigo, string $nombre): object
    {
        if (isset($this->sedeCache[$codigo])) {
            return $this->sedeCache[$codigo];
        }

        throw new \RuntimeException(
            "La sede con código \"{$codigo}\" ({$nombre}) no existe. Regístrela primero en el módulo de Sedes."
        );
    }

    private function resolverFacultad(string $nombre): object
    {
        $normEntrada = $this->normalizar($nombre);

        if (isset($this->facultadCache[$normEntrada])) {
            return $this->facultadCache[$normEntrada];
        }

        $claveFuzzy = $this->buscarFuzzy($normEntrada, array_keys($this->facultadCache));
        if ($claveFuzzy !== null) {
            return $this->facultadCache[$claveFuzzy];
        }

        $nombreLimpio = mb_strtoupper(trim($nombre));
        $id = DB::table('facultad')->insertGetId([
            'nombre'     => $nombreLimpio,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $facultad = DB::table('facultad')->where('id_facultad', $id)->first();
        $this->facultadCache[$normEntrada] = $facultad;

        return $facultad;
    }

    private function resolverFacultadSede(int $idFacultad, int $idSede): void
    {
        $existe = DB::table('facultad_sede')
            ->where('id_facultad', $idFacultad)
            ->where('id_sede', $idSede)
            ->exists();

        if (! $existe) {
            DB::table('facultad_sede')->insert([
                'id_facultad' => $idFacultad,
                'id_sede'     => $idSede,
            ]);
        }
    }

    private function resolverPrograma(string $nombre, int $idFacultad): object
    {
        $normEntrada = $nombre === '' ? '' : $this->normalizar($nombre);
        $claveExacta = $idFacultad . '|' . $normEntrada;

        if (isset($this->programaCache[$claveExacta])) {
            return $this->programaCache[$claveExacta];
        }

        $clavesDeEstaFacultad = array_filter(
            array_keys($this->programaCache),
            fn($k) => str_starts_with($k, $idFacultad . '|')
        );
        $nombresNorm = array_map(
            fn($k) => substr($k, strlen($idFacultad . '|')),
            array_values($clavesDeEstaFacultad)
        );

        $normFuzzy = $this->buscarFuzzy($normEntrada, $nombresNorm);
        if ($normFuzzy !== null) {
            $clave = $idFacultad . '|' . $normFuzzy;
            return $this->programaCache[$clave];
        }

        $nombreLimpio = mb_strtoupper(trim($nombre));
        $id = DB::table('programa')->insertGetId([
            'id_facultad'      => $idFacultad,
            'id_tipo_formacion'=> null,
            'nombre'           => $nombreLimpio,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $programa = DB::table('programa')->where('id_programa', $id)->first();
        $this->programaCache[$claveExacta] = $programa;

        return $programa;
    }

    private function resolverProgramaSede(int $idPrograma, int $idSede): int
    {
        $clave = $idPrograma . '|' . $idSede;

        if (isset($this->progSedeCache[$clave])) {
            return $this->progSedeCache[$clave];
        }

        $id = DB::table('programa_sede')->insertGetId([
            'id_programa' => $idPrograma,
            'id_sede'     => $idSede,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->progSedeCache[$clave] = $id;
        return $id;
    }

    private function resolverPlanEstudio(int $idProgramaSede, string $codigoPlan): int
    {
        $clave = $idProgramaSede . '|' . $codigoPlan;

        if (isset($this->planCache[$clave])) {
            return $this->planCache[$clave];
        }

        $id = DB::table('plan_estudio')->insertGetId([
            'id_programa_sede' => $idProgramaSede,
            'codigo_plan'      => $codigoPlan,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $this->planCache[$clave] = $id;
        return $id;
    }

    private function resolverUsuario(
        string $documento,
        string $nombres,
        string $apellidos,
        string $email
    ): array {
        [$primerNombre, $segundoNombre]    = $this->partirNombre($nombres);
        [$primerApellido, $segundoApellido] = $this->partirNombre($apellidos);

        $existente = DB::table('usuario')->where('documento', $documento)->first();

        $datos = [
            'primer_nombre'    => mb_strtoupper($primerNombre),
            'segundo_nombre'   => $segundoNombre ? mb_strtoupper($segundoNombre) : null,
            'primer_apellido'  => mb_strtoupper($primerApellido),
            'segundo_apellido' => $segundoApellido ? mb_strtoupper($segundoApellido) : null,
            'correo'           => mb_strtolower($email),
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

    private function resolverEstudianteEgresado(int $idUrs, int $idPlan): void
    {
        $existe = DB::table('estudiante_egresado')
            ->where('id_usuario_rol_sede', $idUrs)
            ->exists();

        if ($existe) {
            DB::table('estudiante_egresado')
                ->where('id_usuario_rol_sede', $idUrs)
                ->update(['id_plan_estudio' => $idPlan]);
        } else {
            DB::table('estudiante_egresado')->insert([
                'id_usuario_rol_sede' => $idUrs,
                'id_plan_estudio'     => $idPlan,
            ]);
        }
    }

    // ─────────────────────────────────────────────
    //  Utilidades
    // ─────────────────────────────────────────────

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

    // ─────────────────────────────────────────────
    //  Reprocesar una sola fila (desde corrección manual)
    // ─────────────────────────────────────────────

    public function procesarFilaIndividual(
        int    $idPeriodo,
        string $documento,
        string $nombres,
        string $apellidos,
        string $email,
        string $codigoSede,
        string $nombreSede,
        string $codigoPlan,
        string $nombrePrograma,
        string $nombreFacultad,
        string $nombreRol = 'Estudiante',
    ): array {
        $this->idPeriodo = $idPeriodo;
        $this->nombreRol = $nombreRol;
        $this->idRol     = (int) DB::table('rol')->where('nombre', $nombreRol)->value('id_rol');

        if (! $this->idRol) {
            return [false, "El rol \"{$nombreRol}\" no existe en el sistema."];
        }

        $this->sedeCache     = [];
        $this->facultadCache = [];
        $this->programaCache = [];
        $this->progSedeCache = [];
        $this->planCache     = [];

        $this->cargarCaches();

        $vacios = [];
        if (empty($documento))      $vacios[] = 'documento';
        if (empty($nombres))        $vacios[] = 'nombres';
        if (empty($apellidos))      $vacios[] = 'apellidos';
        if (empty($email))          $vacios[] = 'email';
        if (empty($codigoSede))     $vacios[] = 'código de sede';
        if (empty($nombreSede))     $vacios[] = 'nombre de sede';
        if (empty($codigoPlan))     $vacios[] = 'código de plan';
        if (empty($nombrePrograma)) $vacios[] = 'nombre del programa';
        if (empty($nombreFacultad)) $vacios[] = 'nombre de la facultad';

        if (! empty($vacios)) {
            $msg = implode(', ', array_map(fn($c) => ucfirst($c) . ' es obligatorio', $vacios)) . '.';
            return [false, $msg];
        }

        try {
            DB::beginTransaction();

            $sede       = $this->resolverSede($codigoSede, $nombreSede);
            $facultad   = $this->resolverFacultad($nombreFacultad);
            $this->resolverFacultadSede($facultad->id_facultad, $sede->id_sede);
            $programa   = $this->resolverPrograma($nombrePrograma, $facultad->id_facultad);
            $idProgSede = $this->resolverProgramaSede($programa->id_programa, $sede->id_sede);
            $idPlan     = $this->resolverPlanEstudio($idProgSede, $codigoPlan);
            [$usuario,] = $this->resolverUsuario($documento, $nombres, $apellidos, $email);
            $idUrs      = $this->resolverUsuarioRolSede($usuario->id_usuario, $sede->id_sede);
            $this->resolverEstudianteEgresado($idUrs, $idPlan);

            DB::commit();
            return [true, null];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [false, $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────
    //  Persistencia de inconsistencias
    // ─────────────────────────────────────────────

    private function guardarInconsistencia(
        int    $idPeriodo,
        string $nombreRol,
        int    $fila,
        string $documento,
        string $nombres,
        string $apellidos,
        string $email,
        string $codigoSede,
        string $nombreSede,
        string $codigoPlan,
        string $nombrePrograma,
        string $nombreFacultad,
        string $error,
    ): void {
        try {
            DB::table('carga_inconsistencia')->insert([
                'id_periodo'      => $idPeriodo,
                'nombre_rol'      => $nombreRol,
                'fila'            => $fila,
                'documento'       => $documento,
                'nombres'         => $nombres,
                'apellidos'       => $apellidos,
                'email'           => $email,
                'codigo_sede'     => $codigoSede,
                'nombre_sede'     => $nombreSede,
                'codigo_plan'     => $codigoPlan,
                'nombre_programa' => $nombrePrograma,
                'nombre_facultad' => $nombreFacultad,
                'error'           => $error,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } catch (\Throwable $ex) {
            Log::error('No se pudo guardar inconsistencia: ' . $ex->getMessage());
        }
    }
}
