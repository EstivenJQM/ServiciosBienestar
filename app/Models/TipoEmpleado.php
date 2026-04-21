<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEmpleado extends Model
{
    protected $table      = 'tipo_empleado';
    protected $primaryKey = 'id_tipo_empleado';
    public    $timestamps = false;

    protected $fillable = ['nombre'];
}
