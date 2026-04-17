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
use App\Http\Controllers\CargaUsuariosController;
use App\Http\Controllers\InconsistenciaController;
use App\Http\Controllers\UsuarioController;

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
Route::get ('programas/asignacion-snies',  [ProgramaController::class, 'asignacionSnies'])
    ->name('programas.asignacion-snies');
Route::post('programas/asignacion-snies',  [ProgramaController::class, 'guardarAsignacionSnies'])
    ->name('programas.asignacion-snies.guardar');
Route::resource('programas', ProgramaController::class)->except(['show']);

Route::resource('periodos',  PeriodoController::class)->except(['show']);
Route::resource('servicios', ServicioController::class)->except(['show']);

Route::get   ('usuarios',                              [UsuarioController::class, 'index'])  ->name('usuarios.index');
Route::get   ('usuarios/{usuario}/edit',              [UsuarioController::class, 'edit'])   ->name('usuarios.edit');
Route::put   ('usuarios/{usuario}',                   [UsuarioController::class, 'update']) ->name('usuarios.update');
Route::delete('usuarios/{usuario}',                   [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
Route::delete('usuarios/{usuario}/roles/{rolSede}',   [UsuarioController::class, 'destroyRol'])->name('usuarios.roles.destroy');

Route::get ('usuarios/carga', [CargaUsuariosController::class, 'index'])
    ->name('usuarios.carga.index');
Route::post('usuarios/carga', [CargaUsuariosController::class, 'store'])
    ->name('usuarios.carga.store');

// Ruta antigua — redirige a la nueva
Route::get('usuarios/carga-estudiantes', fn() => redirect()->route('usuarios.carga.index'))
    ->name('usuarios.carga-estudiantes.index');

// Inconsistencias de carga (definidas antes de rutas con {usuario})
Route::get   ('usuarios/inconsistencias',                          [InconsistenciaController::class, 'index'])     ->name('usuarios.inconsistencias.index');
Route::get   ('usuarios/inconsistencias/{inconsistencia}/edit',    [InconsistenciaController::class, 'edit'])      ->name('usuarios.inconsistencias.edit');
Route::put   ('usuarios/inconsistencias/{inconsistencia}',         [InconsistenciaController::class, 'update'])    ->name('usuarios.inconsistencias.update');
Route::delete('usuarios/inconsistencias/{inconsistencia}',         [InconsistenciaController::class, 'destroy'])   ->name('usuarios.inconsistencias.destroy');
Route::delete('usuarios/inconsistencias',                          [InconsistenciaController::class, 'destroyAll'])->name('usuarios.inconsistencias.destroy-all');
