<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargaInconsistencia extends Model
{
    protected $table      = 'carga_inconsistencia';
    protected $primaryKey = 'id_inconsistencia';

    protected $fillable = [
        'id_periodo',
        'nombre_rol',
        'documento',
        'nombres',
        'apellidos',
        'email',
        'codigo_sede',
        'nombre_sede',
        'dependencia',
        'codigo_cargo',
        'nombre_cargo',
        'codigo_plan',
        'nombre_programa',
        'nombre_facultad',
        'error',
        'fila',
    ];

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }

    /**
     * Intenta detectar qué campo del formulario causó el error
     * para resaltarlo visualmente.
     */
    public function campoError(): ?string
    {
        $e = mb_strtolower($this->error);

        return match(true) {
            str_contains($e, 'cargo')       => 'nombre_cargo',
            str_contains($e, 'dependencia') => 'dependencia',
            str_contains($e, 'sede')        => in_array($this->nombre_rol, ['Contratista', 'Administrativo']) ? 'nombre_sede' : 'codigo_sede',
            str_contains($e, 'facultad')    => 'nombre_facultad',
            str_contains($e, 'programa')    => 'nombre_programa',
            str_contains($e, 'plan')        => 'codigo_plan',
            str_contains($e, 'documento')   => 'documento',
            str_contains($e, 'email') || str_contains($e, 'correo') => 'email',
            default                         => null,
        };
    }
}
