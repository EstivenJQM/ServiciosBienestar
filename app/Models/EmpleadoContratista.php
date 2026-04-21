<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoContratista extends Model
{
    protected $table      = 'empleado_contratista';
    protected $primaryKey = 'id_usuario_rol_sede';
    public    $incrementing = false;
    public    $timestamps   = false;

    protected $fillable = ['id_usuario_rol_sede', 'id_dependencia', 'id_cargo', 'codigo_cargo'];

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia', 'id_dependencia');
    }
}
