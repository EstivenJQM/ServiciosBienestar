<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CargaEstudiantesService
{
    // Caches en memoria para evitar queries repetidas
    private array $sedeCache     = [];   // codigo        => stdClass{id_sede, nombre}
    private array $facultadCache = [];   // normalizado   => stdClass{id_facultad, nombre}
    private array $programaCache = [];   // "idFac|norm"  => stdClass{id_programa, nombre}
    private array $progSedeCache = [];   // "idProg|idSed"=> id_programa_sede
    private array $planCache     = [];   // "idPS|codigo" => id_plan_estudio

    private int $idRolEstudiante;
    private int $idPeriodo;

    // ─────────────────────────────────────────────
    //  Punto de entrada
    // ─────────────────────────────────────────────
    public function cargar(UploadedFile $archivo, int $idPeriodo): array
    {
        set_time_limit(300);

        $this->idPeriodo      = $idPeriodo;
        $this->idRolEstudiante = (int) DB::table('rol')
            ->where('nombre', 'Estudiante')
            ->value('id_rol');

        $this->cargarCaches();

        $handle = fopen($archivo->getRealPath(), 'r');

        // Quitar BOM UTF-8 si existe
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Detectar separador con la primera línea (cabecera)
        $cabecera = fgets($handle);
        $sep = str_contains($cabecera, ';') ? ';' : ',';
        // No usamos la cabecera, el ciclo lee desde la 2.ª línea

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

            if (empty($documento)) {
                $errores[] = "Fila {$fila}: documento vacío.";
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
                $errores[] = "Fila {$fila} (doc: {$documento}): " . $e->getMessage();
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
        // Sedes: indexadas por código
        foreach (DB::table('sede')->get() as $s) {
            $this->sedeCache[(string) $s->codigo] = $s;
        }

        // Facultades: indexadas por nombre normalizado
        foreach (DB::table('facultad')->get() as $f) {
            $this->facultadCache[$this->normalizar($f->nombre)] = $f;
        }

        // Programas: indexados por "id_facultad|nombre_normalizado"
        foreach (DB::table('programa')->get() as $p) {
            $clave = $p->id_facultad . '|' . $this->normalizar($p->nombre);
            $this->programaCache[$clave] = $p;
        }

        // programa_sede: indexado por "id_programa|id_sede"
        foreach (DB::table('programa_sede')->get() as $ps) {
            $clave = $ps->id_programa . '|' . $ps->id_sede;
            $this->progSedeCache[$clave] = $ps->id_programa_sede;
        }

        // plan_estudio: indexado por "id_programa_sede|codigo_plan"
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

        $id = DB::table('sede')->insertGetId([
            'codigo'     => $codigo,
            'nombre'     => mb_strtoupper(trim($nombre)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sede = DB::table('sede')->where('id_sede', $id)->first();
        $this->sedeCache[$codigo] = $sede;

        return $sede;
    }

    private function resolverFacultad(string $nombre): object
    {
        $normEntrada = $this->normalizar($nombre);

        // Buscar exacto
        if (isset($this->facultadCache[$normEntrada])) {
            return $this->facultadCache[$normEntrada];
        }

        // Buscar fuzzy
        $claveFuzzy = $this->buscarFuzzy($normEntrada, array_keys($this->facultadCache));
        if ($claveFuzzy !== null) {
            return $this->facultadCache[$claveFuzzy];
        }

        // Crear nueva
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

        // Fuzzy dentro de la misma facultad
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

        // Crear nuevo
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
            ->where('id_rol',     $this->idRolEstudiante)
            ->where('id_sede',    $idSede)
            ->where('id_periodo', $this->idPeriodo)
            ->value('id_usuario_rol_sede');

        if ($existente) {
            return $existente;
        }

        return DB::table('usuario_rol_sede')->insertGetId([
            'id_usuario'  => $idUsuario,
            'id_rol'      => $this->idRolEstudiante,
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
        // Quitar tildes y caracteres especiales
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        // Colapsar espacios múltiples
        return preg_replace('/\s+/', ' ', $texto);
    }

    /**
     * Busca el mejor candidato en $opciones con similitud >= $umbral%.
     * Devuelve la clave del candidato o null si ninguno supera el umbral.
     */
    private function buscarFuzzy(string $entrada, array $opciones, float $umbral = 82.0): ?string
    {
        if (empty($opciones)) return null;

        $mejorPct  = 0.0;
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

    /** Devuelve [primer_nombre, segundo_nombre|null] */
    private function partirNombre(string $texto): array
    {
        $partes = preg_split('/\s+/', trim($texto), 2);
        return [
            $partes[0] ?? '',
            isset($partes[1]) && $partes[1] !== '' ? $partes[1] : null,
        ];
    }
}
