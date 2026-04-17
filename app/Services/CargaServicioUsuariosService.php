<?php

namespace App\Services;

use App\Models\Servicio;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CargaServicioUsuariosService
{
    public function asignar(UploadedFile $archivo, Servicio $servicio): array
    {
        $handle = fopen($archivo->getRealPath(), 'r');

        // Quitar BOM UTF-8
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Detectar separador y descartar cabecera
        $cabecera = fgets($handle);
        $sep = str_contains($cabecera, ';') ? ';' : ',';

        $asignados     = 0;
        $yaExistian    = 0;
        $noEncontrados = [];
        $sinRol        = [];
        $fila          = 1;

        while (($cols = fgetcsv($handle, 0, $sep)) !== false) {
            $fila++;

            if (count($cols) < 2) {
                $noEncontrados[] = "Fila {$fila}: formato incorrecto (se esperan 2 columnas).";
                continue;
            }

            [$documento, $nombreRol] = array_map('trim', array_slice($cols, 0, 2));

            if (empty($documento) || empty($nombreRol)) {
                $noEncontrados[] = "Fila {$fila}: documento o rol vacío.";
                continue;
            }

            $usuario = DB::table('usuario')->where('documento', $documento)->first();

            if (! $usuario) {
                $noEncontrados[] = "{$documento} (no registrado)";
                continue;
            }

            $urs = DB::table('usuario_rol_sede as urs')
                ->join('rol', 'urs.id_rol', '=', 'rol.id_rol')
                ->where('urs.id_usuario', $usuario->id_usuario)
                ->where('urs.id_periodo', $servicio->id_periodo)
                ->where('rol.nombre',     $nombreRol)
                ->where('urs.estado',     'activo')
                ->value('urs.id_usuario_rol_sede');

            if (! $urs) {
                $nombre   = trim("{$usuario->primer_nombre} {$usuario->primer_apellido}");
                $sinRol[] = "{$documento} — {$nombre} (sin rol «{$nombreRol}» activo en el período)";
                continue;
            }

            // Verificar si el usuario (sin importar el rol) ya está en este servicio
            $yaAsignado = DB::table('servicio_usuario as su')
                ->join('usuario_rol_sede as urs2', 'urs2.id_usuario_rol_sede', '=', 'su.id_usuario_rol_sede')
                ->where('su.id_servicio',  $servicio->id_servicio)
                ->where('urs2.id_usuario', $usuario->id_usuario)
                ->exists();

            if ($yaAsignado) {
                $yaExistian++;
                continue;
            }

            DB::table('servicio_usuario')->insert([
                'id_servicio'         => $servicio->id_servicio,
                'id_usuario_rol_sede' => $urs,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            $asignados++;
        }

        fclose($handle);

        return [
            'asignados'      => $asignados,
            'ya_existian'    => $yaExistian,
            'no_encontrados' => $noEncontrados,
            'sin_rol'        => $sinRol,
            'total'          => $fila - 1,
        ];
    }
}
