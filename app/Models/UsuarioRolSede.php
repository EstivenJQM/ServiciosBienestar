<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioRolSede extends Model
{
    protected $table = 'usuario_rol_sede';
    protected $primaryKey = 'id_usuario_rol_sede';

    protected $fillable = ['id_usuario', 'id_rol', 'id_sede', 'id_periodo', 'estado'];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function estudianteEgresado()
    {
        return $this->hasOne(EstudianteEgresado::class, 'id_usuario_rol_sede', 'id_usuario_rol_sede');
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'id_usuario_rol_sede', 'id_usuario_rol_sede');
    }
}
