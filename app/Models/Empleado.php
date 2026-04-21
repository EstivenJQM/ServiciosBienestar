<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table      = 'empleado';
    protected $primaryKey = 'id_usuario_rol_sede';
    public    $incrementing = false;
    public    $timestamps   = false;

    protected $fillable = ['id_usuario_rol_sede', 'id_tipo_empleado'];

    public function tipoEmpleado()
    {
        return $this->belongsTo(TipoEmpleado::class, 'id_tipo_empleado', 'id_tipo_empleado');
    }

    public function contratista()
    {
        return $this->hasOne(EmpleadoContratista::class, 'id_usuario_rol_sede', 'id_usuario_rol_sede');
    }
}
