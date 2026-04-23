<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table      = 'servicio';
    protected $primaryKey = 'id_servicio';

    protected $fillable = [
        'id_linea',
        'id_tipo_actividad',
        'nombre',
        'id_sede',
        'fecha',
        'id_periodo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'id_linea', 'id_linea');
    }

    public function tipoActividad()
    {
        return $this->belongsTo(TipoActividad::class, 'id_tipo_actividad', 'id_tipo_actividad');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }

    public function usuariosAsignados()
    {
        return $this->belongsToMany(
            UsuarioRolSede::class,
            'servicio_usuario',
            'id_servicio',
            'id_usuario_rol_sede'
        )->with(['usuario', 'rol', 'sede', 'empleado.tipoEmpleado', 'empleado.cargo']);
    }
}
