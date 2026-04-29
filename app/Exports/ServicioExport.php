<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServicioExport
{
    private const SERVICIO_HEADERS = [
        'Área', 'Componente', 'Línea', 'Tipo Actividad',
        'Nombre', 'Fecha', 'Período', 'Código Sede', 'Sede',
    ];

    private const TAB_COLORS = [
        'resumen'         => 'FF196844',
        'estudiantes'     => 'FF2E7D32',
        'graduados'       => 'FF66BB6A',
        'por_servicios'   => 'FF196844',
        'administrativos' => 'FF42A5F5',
        'contratistas'    => 'FF90CAF9',
        'docentes'        => 'FFEF6C00',
        'planta'          => 'FFFF9800',
        'ocasional'       => 'FFFFB74D',
        'catedra'         => 'FFFFE0B2',
        'familiares'      => 'FF7B1FA2',
    ];

    public function __construct(
        private readonly Collection $servicios,
        private readonly array $hojas,
    ) {}

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setTitle('Reporte de Servicios');
        $spreadsheet->removeSheetByIndex(0);

        foreach ($this->hojas as $hoja) {
            match ($hoja) {
                'resumen'         => $this->buildResumen($spreadsheet),
                'por_servicios'   => $this->buildPorServicios($spreadsheet),
                'estudiantes'     => $this->buildAcademico($spreadsheet, 'Estudiante', 'Estudiantes', 'estudiantes'),
                'graduados'       => $this->buildAcademico($spreadsheet, 'Graduado', 'Graduados', 'graduados'),
                'administrativos' => $this->buildEmpleado($spreadsheet, 'Administrativo', 'Administrativos', 'administrativos'),
                'contratistas'    => $this->buildEmpleado($spreadsheet, 'Contratista', 'Contratistas', 'contratistas'),
                'docentes'        => $this->buildDocentes($spreadsheet),
                'familiares'      => $this->buildFamiliares($spreadsheet),
                default           => null,
            };
        }

        return $spreadsheet;
    }

    // ── Sheet builders ────────────────────────────────────────────

    private function buildResumen(Spreadsheet $ss): void
    {
        $headers = [...self::SERVICIO_HEADERS, 'Total Personas Impactadas'];

        $rows = $this->servicios->map(fn($s) => [
            ...$this->serviceRow($s),
            $s->usuariosAsignados->pluck('id_usuario')->unique()->count(),
        ])->values()->toArray();

        $this->addWorksheet($ss, 'Resumen', $headers, $rows, 'resumen');
    }

    private function buildPorServicios(Spreadsheet $ss): void
    {
        $headers = [...self::SERVICIO_HEADERS, 'Documento', 'Nombre Completo', 'Correo', 'Sede Beneficiario', 'Rol'];
        $rows    = [];

        foreach ($this->servicios as $s) {
            $base = $this->serviceRow($s);
            if ($s->usuariosAsignados->isEmpty()) {
                $rows[] = [...$base, '', '', '', '', ''];
                continue;
            }
            foreach ($s->usuariosAsignados as $urs) {
                $u       = $urs->usuario;
                $tipoEmp = $urs->empleado?->tipoEmpleado?->nombre;
                $rol     = match (true) {
                    $tipoEmp === 'Docente'                              => $urs->empleado->cargo?->nombre ?? 'Docente',
                    in_array($tipoEmp, ['Administrativo', 'Contratista',
                        'Planta', 'Ocasional', 'Cátedra'], true)       => $tipoEmp,
                    default                                             => $urs->rol?->nombre ?? '',
                };

                $rows[] = [
                    ...$base,
                    $u?->documento        ?? '',
                    $u?->nombre_completo  ?? '',
                    $u?->correo           ?? '',
                    $urs->sede?->nombre   ?? '',
                    $rol,
                ];
            }
        }

        $this->addWorksheet($ss, 'Por Servicios', $headers, $rows, 'por_servicios');
    }

    private function buildAcademico(Spreadsheet $ss, string $rol, string $title, string $key): void
    {
        $headers = [
            ...self::SERVICIO_HEADERS,
            'Documento', 'Nombre Completo', 'Correo', 'Sede Beneficiario',
            'Facultad', 'Programa', 'Plan de Estudio', 'SNIES',
        ];
        $rows = [];

        foreach ($this->servicios as $s) {
            $base  = $this->serviceRow($s);
            $users = $s->usuariosAsignados->filter(fn($urs) => $urs->rol?->nombre === $rol);

            foreach ($users as $urs) {
                $u    = $urs->usuario;
                $plan = $urs->estudianteEgresado?->planEstudio;
                $ps   = $plan?->programaSede;
                $prog = $ps?->programa;

                $rows[] = [
                    ...$base,
                    $u?->documento        ?? '',
                    $u?->nombre_completo  ?? '',
                    $u?->correo           ?? '',
                    $urs->sede?->nombre   ?? '',
                    $prog?->facultad?->nombre ?? '',
                    $prog?->nombre            ?? '',
                    $plan?->codigo_plan       ?? '',
                    $ps?->codigo_snies        ?? '',
                ];
            }
        }

        $this->addWorksheet($ss, $title, $headers, $rows, $key);
    }

    private function buildEmpleado(Spreadsheet $ss, string $tipo, string $title, string $key): void
    {
        $headers = [
            ...self::SERVICIO_HEADERS,
            'Documento', 'Nombre Completo', 'Correo', 'Sede Beneficiario',
            'Dependencia', 'Código Cargo', 'Cargo',
        ];
        $rows = [];

        foreach ($this->servicios as $s) {
            $base  = $this->serviceRow($s);
            $users = $s->usuariosAsignados->filter(
                fn($urs) => $urs->empleado?->tipoEmpleado?->nombre === $tipo
            );

            foreach ($users as $urs) {
                $u   = $urs->usuario;
                $emp = $urs->empleado;

                $rows[] = [
                    ...$base,
                    $u?->documento        ?? '',
                    $u?->nombre_completo  ?? '',
                    $u?->correo           ?? '',
                    $urs->sede?->nombre   ?? '',
                    $emp?->dependencia?->nombre ?? '',
                    $emp?->cargo?->codigo       ?? '',
                    $emp?->cargo?->nombre       ?? '',
                ];
            }
        }

        $this->addWorksheet($ss, $title, $headers, $rows, $key);
    }

    private function buildDocentes(Spreadsheet $ss): void
    {
        $headers = [
            ...self::SERVICIO_HEADERS,
            'Documento', 'Nombre Completo', 'Correo', 'Sede Beneficiario',
            'Código Cargo', 'Cargo', 'Dependencia',
        ];
        $rows = [];

        foreach ($this->servicios as $s) {
            $base  = $this->serviceRow($s);
            $users = $s->usuariosAsignados->filter(
                fn($urs) => $urs->empleado?->tipoEmpleado?->nombre === 'Docente'
            );

            foreach ($users as $urs) {
                $u   = $urs->usuario;
                $emp = $urs->empleado;

                $rows[] = [
                    ...$base,
                    $u?->documento        ?? '',
                    $u?->nombre_completo  ?? '',
                    $u?->correo           ?? '',
                    $urs->sede?->nombre   ?? '',
                    $emp?->cargo?->codigo       ?? '',
                    $emp?->cargo?->nombre       ?? '',
                    $emp?->dependencia?->nombre ?? '',
                ];
            }
        }

        $this->addWorksheet($ss, 'Docentes', $headers, $rows, 'docentes');
    }

    private function buildFamiliares(Spreadsheet $ss): void
    {
        $headers = [
            ...self::SERVICIO_HEADERS,
            'Documento', 'Nombre Completo', 'Correo', 'Sede Beneficiario',
        ];
        $rows = [];

        foreach ($this->servicios as $s) {
            $base  = $this->serviceRow($s);
            $users = $s->usuariosAsignados->filter(fn($urs) => $urs->rol?->nombre === 'Familiar');

            foreach ($users as $urs) {
                $u = $urs->usuario;
                $rows[] = [
                    ...$base,
                    $u?->documento       ?? '',
                    $u?->nombre_completo ?? '',
                    $u?->correo          ?? '',
                    $urs->sede?->nombre  ?? '',
                ];
            }
        }

        $this->addWorksheet($ss, 'Familiares', $headers, $rows, 'familiares');
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function serviceRow($s): array
    {
        return [
            $s->linea->componente->area->nombre,
            $s->linea->componente->nombre,
            $s->linea->nombre,
            $s->tipoActividad->nombre,
            $s->nombre,
            $s->fecha->format('d/m/Y'),
            $s->periodo?->nombre ?? '',
            $s->sede->codigo,
            $s->sede->nombre,
        ];
    }

    private function addWorksheet(Spreadsheet $ss, string $title, array $headers, array $rows, string $key): void
    {
        $ws = new Worksheet($ss, $title);
        $ss->addSheet($ws);

        // Header row
        $ws->fromArray([$headers], null, 'A1');

        $colCount = count($headers);
        $lastCol  = Coordinate::stringFromColumnIndex($colCount);

        $ws->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF196844']],
        ]);

        // Tab color
        $ws->getTabColor()->setARGB(self::TAB_COLORS[$key] ?? 'FF196844');

        // Data
        if (!empty($rows)) {
            $ws->fromArray($rows, null, 'A2');
        }

        // Freeze header
        $ws->freezePane('A2');

        // Column widths
        $colWidths = [18, 18, 18, 18, 32, 12, 12, 14, 20];
        for ($i = 1; $i <= $colCount; $i++) {
            $letter = Coordinate::stringFromColumnIndex($i);
            $width  = $colWidths[$i - 1] ?? 22;
            $ws->getColumnDimension($letter)->setWidth($width);
        }
    }
}
