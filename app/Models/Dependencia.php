<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    protected $table      = 'dependencia';
    protected $primaryKey = 'id_dependencia';

    protected $fillable = ['nombre'];
}
