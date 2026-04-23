<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table      = 'empleado';
    protected $primaryKey = 'id_usuario_rol_sede';
    public    $incrementing = false;
    public    $timestamps   = false;

    protected $fillable = ['id_usuario_rol_sede', 'id_tipo_empleado', 'id_dependencia', 'id_cargo'];

    public function tipoEmpleado()
    {
        return $this->belongsTo(TipoEmpleado::class, 'id_tipo_empleado', 'id_tipo_empleado');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia', 'id_dependencia');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo', 'id_cargo');
    }
}
