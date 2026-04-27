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
use App\Http\Controllers\DependenciaController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\InconsistenciaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.alt');

Route::resource('areas',      AreaController::class)->except(['show']);
Route::resource('componentes', ComponenteController::class)->except(['show']);
Route::resource('lineas',         LineaController::class)->except(['show']);
Route::resource('tipo-actividad', TipoActividadController::class)->except(['show']);

Route::resource('sedes',        SedeController::class)->except(['show']);
Route::resource('dependencias', DependenciaController::class)->except(['show']);
Route::resource('cargos',       CargoController::class)->except(['show']);
Route::resource('facultades', FacultadController::class)->except(['show'])
    ->parameters(['facultades' => 'facultad']);
Route::get ('programas/asignacion-snies',  [ProgramaController::class, 'asignacionSnies'])
    ->name('programas.asignacion-snies');
Route::post('programas/asignacion-snies',  [ProgramaController::class, 'guardarAsignacionSnies'])
    ->name('programas.asignacion-snies.guardar');
Route::resource('programas', ProgramaController::class)->except(['show']);

Route::resource('periodos',  PeriodoController::class)->except(['show']);

Route::get('servicios/reportes',           [ServicioController::class, 'reportes'])            ->name('servicios.reportes');
Route::get('servicios/reportes/programas', [ServicioController::class, 'programasPorFacultad'])->name('servicios.reportes.programas');
Route::get('servicios/reportes/planes',    [ServicioController::class, 'planesPorPrograma'])   ->name('servicios.reportes.planes');
Route::get('servicios/reportes/descargar', [ServicioController::class, 'descargar'])           ->name('servicios.reportes.descargar');

Route::resource('servicios', ServicioController::class);
Route::post  ('servicios/{servicio}/usuarios',      [ServicioController::class, 'asignarUsuarios'])   ->name('servicios.usuarios.store');
Route::delete('servicios/{servicio}/usuarios/{urs}', [ServicioController::class, 'desasignarUsuario'])->name('servicios.usuarios.destroy');

// Rutas estáticas de usuarios (antes de las rutas con parámetros {usuario})
Route::get ('usuarios/carga', [CargaUsuariosController::class, 'index'])
    ->name('usuarios.carga.index');
Route::post('usuarios/carga', [CargaUsuariosController::class, 'store'])
    ->name('usuarios.carga.store');

Route::get('usuarios/carga-estudiantes', fn() => redirect()->route('usuarios.carga.index'))
    ->name('usuarios.carga-estudiantes.index');

Route::get   ('usuarios/inconsistencias',                          [InconsistenciaController::class, 'index'])     ->name('usuarios.inconsistencias.index');
Route::get   ('usuarios/inconsistencias/{inconsistencia}/edit',    [InconsistenciaController::class, 'edit'])      ->name('usuarios.inconsistencias.edit');
Route::put   ('usuarios/inconsistencias/{inconsistencia}',         [InconsistenciaController::class, 'update'])    ->name('usuarios.inconsistencias.update');
Route::delete('usuarios/inconsistencias/{inconsistencia}',         [InconsistenciaController::class, 'destroy'])   ->name('usuarios.inconsistencias.destroy');
Route::delete('usuarios/inconsistencias',                          [InconsistenciaController::class, 'destroyAll'])->name('usuarios.inconsistencias.destroy-all');

Route::get   ('usuarios',                              [UsuarioController::class, 'index'])  ->name('usuarios.index');
Route::get   ('usuarios/{usuario}/edit',              [UsuarioController::class, 'edit'])   ->name('usuarios.edit');
Route::put   ('usuarios/{usuario}',                   [UsuarioController::class, 'update']) ->name('usuarios.update');
Route::delete('usuarios/{usuario}',                   [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
Route::delete('usuarios/{usuario}/roles/{rolSede}',   [UsuarioController::class, 'destroyRol'])->name('usuarios.roles.destroy');
