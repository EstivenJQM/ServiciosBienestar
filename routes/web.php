<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ComponenteController;
use App\Http\Controllers\LineaController;
use App\Http\Controllers\TipoActividadController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\FacultadController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\CargaEstudiantesController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('areas',      AreaController::class)->except(['show']);
Route::resource('componentes', ComponenteController::class)->except(['show']);
Route::resource('lineas',         LineaController::class)->except(['show']);
Route::resource('tipo-actividad', TipoActividadController::class)->except(['show']);

Route::resource('sedes',     SedeController::class)->except(['show']);
Route::resource('facultades', FacultadController::class)->except(['show'])
    ->parameters(['facultades' => 'facultad']);
Route::resource('programas', ProgramaController::class)->except(['show']);

Route::resource('periodos',  PeriodoController::class)->except(['show']);
Route::resource('servicios', ServicioController::class)->except(['show']);

Route::get ('usuarios/carga-estudiantes',        [CargaEstudiantesController::class, 'index'])
    ->name('usuarios.carga-estudiantes.index');
Route::post('usuarios/carga-estudiantes',        [CargaEstudiantesController::class, 'store'])
    ->name('usuarios.carga-estudiantes.store');
