<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $table      = 'cargo';
    protected $primaryKey = 'id_cargo';

    protected $fillable = ['codigo', 'nombre'];
}
