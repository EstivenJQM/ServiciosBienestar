<x-layout title="Reportes de Servicios">

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .panel-benef-section { transition: all .2s ease; }
    .filter-card-header {
        cursor: pointer;
        user-select: none;
        background: #f8f9fa;
        border-radius: .375rem;
    }
    .filter-card-header:hover { background: #e9ecef; }
    .check-role, .check-tipo-emp { cursor: pointer; }
    .check-hoja { accent-color: #196844; width: .85em; height: .85em; border-radius: 50%; flex-shrink: 0; cursor: pointer; }
</style>
@endpush

    {{-- ── Cabecera ── --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-file-earmark-bar-graph-fill me-2" style="color:#196844"></i>Reportes
        </h2>
    </div>

    <form method="GET" action="{{ route('servicios.reportes') }}" id="form-reportes">

        {{-- ══════════════════════════════════════════════════════
             PANEL A — Filtros de Servicio
        ══════════════════════════════════════════════════════ --}}
        @php
            $nFiltrosServicio = (int)(!empty($idPeriodos)) + (int)(!empty($idSedes))
                + (int)(!empty($idAreas)) + (int)(!empty($idComponentes))
                + (int)(!empty($idLineas)) + (int)(!empty($idTiposActividad))
                + (int)($fechaDesde !== null && $fechaDesde !== '')
                + (int)($fechaHasta !== null && $fechaHasta !== '')
                + (int)(!empty($nombresServicios));
        @endphp

        <div class="card mb-3 shadow-sm">
            <div class="card-header filter-card-header d-flex justify-content-between align-items-center py-2"
                 data-bs-toggle="collapse" data-bs-target="#panel-servicio">
                <span class="fw-semibold" style="font-size:.95rem">
                    <i class="bi bi-clipboard2-heart-fill me-2" style="color:#196844"></i>Filtros de Servicio
                </span>
                <div class="d-flex align-items-center gap-2">
                    @if($nFiltrosServicio > 0)
                        <span class="badge rounded-pill" style="background:#196844">{{ $nFiltrosServicio }}</span>
                    @endif
                    <i class="bi bi-chevron-down"></i>
                </div>
            </div>
            <div class="collapse show" id="panel-servicio">
                <div class="card-body py-3">
                    <div class="row g-3">

                        {{-- Búsqueda por nombre --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                <i class="bi bi-search me-1"></i>Nombre del servicio
                            </label>
                            <select name="nombre_servicio[]" multiple id="r-servicio" data-placeholder="Todos los servicios">
                                @foreach($nombresServiciosDisponibles as $nombre)
                                    <option value="{{ $nombre }}" {{ in_array($nombre, $nombresServicios) ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Período --}}
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                <i class="bi bi-calendar3 me-1"></i>Período
                            </label>
                            <select name="id_periodo[]" multiple id="r-periodo" data-placeholder="Todos">
                                @foreach($periodos as $p)
                                    <option value="{{ $p->id_periodo }}" {{ in_array($p->id_periodo, $idPeriodos) ? 'selected' : '' }}>
                                        {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sede --}}
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                <i class="bi bi-geo-alt me-1"></i>Sede
                            </label>
                            <select name="id_sede[]" multiple id="r-sede" data-placeholder="Todas">
                                @foreach($sedes as $sede)
                                    <option value="{{ $sede->id_sede }}" {{ in_array($sede->id_sede, $idSedes) ? 'selected' : '' }}>
                                        [{{ $sede->codigo }}] {{ $sede->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Área --}}
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                <i class="bi bi-diagram-3-fill me-1"></i>Área
                            </label>
                            <select name="id_area[]" multiple id="r-area" data-placeholder="Todas">
                                @foreach($areas as $area)
                                    <option value="{{ $area->id_area }}" {{ in_array($area->id_area, $idAreas) ? 'selected' : '' }}>
                                        {{ $area->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Componente --}}
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                <i class="bi bi-collection-fill me-1"></i>Componente
                            </label>
                            <select name="id_componente[]" multiple id="r-componente" data-placeholder="Todos">
                                @foreach($componentes as $comp)
                                    <option value="{{ $comp->id_componente }}"
                                            data-area="{{ $comp->id_area }}"
                                            {{ in_array($comp->id_componente, $idComponentes) ? 'selected' : '' }}>
                                        {{ $comp->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Línea --}}
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                <i class="bi bi-list-ul me-1"></i>Línea
                            </label>
                            <select name="id_linea[]" multiple id="r-linea" data-placeholder="Todas">
                                @foreach($lineas as $linea)
                                    <option value="{{ $linea->id_linea }}"
                                            data-componente="{{ $linea->id_componente }}"
                                            {{ in_array($linea->id_linea, $idLineas) ? 'selected' : '' }}>
                                        {{ $linea->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tipo actividad --}}
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                <i class="bi bi-tags-fill me-1"></i>Tipo de Actividad
                            </label>
                            <select name="id_tipo_actividad[]" multiple id="r-tipo-actividad" data-placeholder="Todos">
                                @foreach($tiposActividad as $tipo)
                                    <option value="{{ $tipo->id_tipo_actividad }}" {{ in_array($tipo->id_tipo_actividad, $idTiposActividad) ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rango fechas --}}
                        <div class="col-12"><hr class="my-0">
                            <p class="small fw-semibold text-muted text-uppercase mb-0 mt-2" style="font-size:.68rem">
                                <i class="bi bi-calendar-range me-1"></i>Rango de fechas
                            </p>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">Desde</label>
                            <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="form-control">
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">Hasta</label>
                            <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="form-control">
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             PANEL B — Filtros de Beneficiarios
        ══════════════════════════════════════════════════════ --}}
        @php
            $nFiltrosBenef = (int)(!empty($roles)) + (int)(!empty($idFacultades))
                + (int)(!empty($idProgramas)) + (int)(!empty($idPlanes))
                + (int)(!empty($tiposEmpleado)) + (int)(!empty($idDependencias))
                + (int)(!empty($idCargos));

            $rolActivos = array_flip($roles);
            $tieneEstudiante = isset($rolActivos['Estudiante']) || isset($rolActivos['Graduado']);
            $tieneEmpleado   = isset($rolActivos['Empleado']);
            $tipoEmpActivos  = array_flip($tiposEmpleado);
        @endphp

        <div class="card mb-3 shadow-sm">
            <div class="card-header filter-card-header d-flex justify-content-between align-items-center py-2"
                 data-bs-toggle="collapse" data-bs-target="#panel-benef">
                <span class="fw-semibold" style="font-size:.95rem">
                    <i class="bi bi-people-fill me-2" style="color:#196844"></i>Filtros de Beneficiarios
                </span>
                <div class="d-flex align-items-center gap-2">
                    @if($nFiltrosBenef > 0)
                        <span class="badge rounded-pill" style="background:#196844">{{ $nFiltrosBenef }}</span>
                    @endif
                    <i class="bi bi-chevron-down"></i>
                </div>
            </div>
            <div class="collapse show" id="panel-benef">
                <div class="card-body py-3">

                    {{-- ── Roles ── --}}
                    <p class="small fw-semibold text-muted text-uppercase mb-2" style="font-size:.68rem">
                        <i class="bi bi-person-badge me-1"></i>Roles a incluir
                        <span class="fw-normal text-muted ms-1">(vacío = todos)</span>
                    </p>
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        @foreach(['Estudiante','Graduado','Empleado'] as $rol)
                            <div class="form-check">
                                <input class="form-check-input check-role" type="checkbox"
                                       name="roles[]" value="{{ $rol }}"
                                       id="rol-{{ $rol }}"
                                       {{ in_array($rol, $roles) ? 'checked' : '' }}>
                                <label class="form-check-label" for="rol-{{ $rol }}">{{ $rol }}</label>
                            </div>
                        @endforeach
                    </div>

                    {{-- ── Sub-panel Estudiante / Graduado ── --}}
                    <div id="sub-estudiante" class="panel-benef-section {{ $tieneEstudiante || empty($roles) ? '' : 'd-none' }}">
                        <div class="border rounded p-3 mb-3" style="background:#f0f9f4;border-color:#196844!important">
                            <p class="small fw-semibold mb-2" style="color:#196844;font-size:.75rem">
                                <i class="bi bi-mortarboard-fill me-1"></i>Datos académicos (Estudiante / Graduado)
                            </p>
                            <div class="row g-3">

                                <div class="col-sm-6 col-md-4">
                                    <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                        <i class="bi bi-building me-1"></i>Facultad
                                    </label>
                                    <select name="id_facultad[]" multiple id="r-facultad" data-placeholder="Todas">
                                        @foreach($facultades as $fac)
                                            <option value="{{ $fac->id_facultad }}" {{ in_array($fac->id_facultad, $idFacultades) ? 'selected' : '' }}>
                                                {{ $fac->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                        <i class="bi bi-journal-bookmark-fill me-1"></i>Programa
                                        <span class="text-muted fw-normal">(selecciona facultad primero)</span>
                                    </label>
                                    <select name="id_programa[]" multiple id="r-programa" data-placeholder="Todos">
                                        @foreach($programasDisponibles as $prog)
                                            <option value="{{ $prog->id_programa }}" {{ in_array($prog->id_programa, $idProgramas) ? 'selected' : '' }}>
                                                {{ $prog->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                        <i class="bi bi-book-fill me-1"></i>Plan de Estudio
                                        <span class="text-muted fw-normal">(selecciona programa primero)</span>
                                    </label>
                                    <select name="id_plan_estudio[]" multiple id="r-plan" data-placeholder="Todos">
                                        @foreach($planesDisponibles as $plan)
                                            <option value="{{ $plan->id_plan_estudio }}" {{ in_array($plan->id_plan_estudio, $idPlanes) ? 'selected' : '' }}>
                                                Plan {{ $plan->codigo_plan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ── Sub-panel Empleado ── --}}
                    <div id="sub-empleado" class="panel-benef-section {{ $tieneEmpleado || empty($roles) ? '' : 'd-none' }}">
                        <div class="border rounded p-3" style="background:#fff8e1;border-color:#ffd400!important">
                            <p class="small fw-semibold mb-2" style="color:#856404;font-size:.75rem">
                                <i class="bi bi-briefcase-fill me-1"></i>Datos de empleado
                            </p>

                            {{-- Tipo empleado --}}
                            <p class="small fw-semibold text-muted text-uppercase mb-2" style="font-size:.68rem">
                                Tipo de empleado <span class="fw-normal">(vacío = todos)</span>
                            </p>
                            <div class="d-flex flex-wrap gap-3 mb-3">
                                @foreach($tiposEmpleadoList as $te)
                                    <div class="form-check">
                                        <input class="form-check-input check-tipo-emp" type="checkbox"
                                               name="tipos_empleado[]" value="{{ $te->nombre }}"
                                               id="te-{{ $te->id_tipo_empleado }}"
                                               {{ in_array($te->nombre, $tiposEmpleado) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="te-{{ $te->id_tipo_empleado }}">{{ $te->nombre }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row g-3">
                                {{-- Dependencia --}}
                                <div class="col-sm-6 col-md-6">
                                    <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                        <i class="bi bi-diagram-3 me-1"></i>Dependencia
                                    </label>
                                    <select name="id_dependencia[]" multiple id="r-dependencia" data-placeholder="Todas">
                                        @foreach($dependencias as $dep)
                                            <option value="{{ $dep->id_dependencia }}" {{ in_array($dep->id_dependencia, $idDependencias) ? 'selected' : '' }}>
                                                {{ $dep->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Cargo --}}
                                <div id="sub-cargo" class="col-sm-6 col-md-6">
                                    <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                                        <i class="bi bi-person-badge me-1"></i>Cargo
                                    </label>
                                    <select name="id_cargo[]" multiple id="r-cargo" data-placeholder="Todos">
                                        @foreach($cargos as $cargo)
                                            <option value="{{ $cargo->id_cargo }}" {{ in_array($cargo->id_cargo, $idCargos) ? 'selected' : '' }}>
                                                {{ $cargo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             PANEL C — Hojas a incluir
        ══════════════════════════════════════════════════════ --}}
        @php
            $hojasInfo = [
                'resumen'         => ['label' => 'Resumen',         'icon' => 'bi-bar-chart-fill',      'color' => '#196844'],
                'por_servicios'   => ['label' => 'Por Servicios',   'icon' => 'bi-people-fill',         'color' => '#0d6efd'],
                'estudiantes'     => ['label' => 'Estudiantes',     'icon' => 'bi-person-fill',         'color' => '#20c997'],
                'graduados'       => ['label' => 'Graduados',       'icon' => 'bi-mortarboard-fill',    'color' => '#6f42c1'],
                'administrativos' => ['label' => 'Administrativos', 'icon' => 'bi-person-gear',         'color' => '#0d6efd'],
                'contratistas'    => ['label' => 'Contratistas',    'icon' => 'bi-file-earmark-person', 'color' => '#6f42c1'],
                'docentes'        => ['label' => 'Docentes',        'icon' => 'bi-person-badge-fill',   'color' => '#fd7e14'],
            ];
            $siempreDisponibles = ['resumen', 'por_servicios'];
        @endphp

        <div class="card mb-3 shadow-sm">
            <div class="card-header filter-card-header d-flex justify-content-between align-items-center py-2"
                 data-bs-toggle="collapse" data-bs-target="#panel-hojas">
                <span class="fw-semibold" style="font-size:.95rem">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-2" style="color:#196844"></i>Hojas a incluir en el Excel
                </span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="collapse show" id="panel-hojas">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-end gap-2 mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-sel-todas">
                            <i class="bi bi-check2-all me-1"></i>Seleccionar todas
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-desel-todas">
                            <i class="bi bi-x-lg me-1"></i>Deseleccionar todas
                        </button>
                    </div>
                    <div class="row g-2" id="hojas-container">
                        @foreach($hojasInfo as $key => $info)
                            @php $disponible = in_array($key, $hojasDisponibles); @endphp
                            <div class="col-sm-6 col-md-4 hoja-row" data-hoja="{{ $key }}" {{ !$disponible ? 'style=display:none' : '' }}>
                                <div class="border rounded p-2 px-3 d-flex align-items-center gap-2" style="background:#f8f9fa">
                                    <input class="check-hoja" type="checkbox"
                                           name="hojas[]" value="{{ $key }}"
                                           id="hoja-{{ $key }}"
                                           {{ in_array($key, $hojasSeleccionadas) ? 'checked' : '' }}
                                           {{ !$disponible ? 'disabled' : '' }}>
                                    <label class="d-flex align-items-center gap-2 flex-grow-1 mb-0" style="cursor:pointer" for="hoja-{{ $key }}">
                                        <i class="bi {{ $info['icon'] }}" style="color:{{ $info['color'] }}"></i>
                                        <span class="fw-semibold" style="font-size:.85rem">{{ $info['label'] }}</span>
                                        @if(in_array($key, $siempreDisponibles))
                                            <span class="badge bg-secondary ms-auto" style="font-size:.6rem">siempre</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             Vista previa + Botones
        ══════════════════════════════════════════════════════ --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

                    {{-- Contadores --}}
                    <div class="d-flex gap-4">
                        <div class="text-center">
                            <div class="fs-3 fw-bold" style="color:#196844">{{ number_format($totalServicios) }}</div>
                            <div class="text-muted small">{{ $totalServicios === 1 ? 'servicio' : 'servicios' }}</div>
                        </div>
                        <div class="vr"></div>
                        <div class="text-center">
                            <div class="fs-3 fw-bold" style="color:#196844">{{ number_format($totalAsignaciones) }}</div>
                            <div class="text-muted small">{{ $totalAsignaciones === 1 ? 'asignación' : 'asignaciones' }}</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        @if($hayFiltros)
                            <a href="{{ route('servicios.reportes') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg me-1"></i>Limpiar
                            </a>
                        @endif

                        <button type="submit" class="btn btn-sibi btn-sm">
                            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar vista previa
                        </button>

                        <button type="submit"
                                formaction="{{ route('servicios.reportes.descargar') }}"
                                class="btn btn-sibi btn-sm {{ $totalServicios === 0 ? 'disabled' : '' }}">
                            <i class="bi bi-file-earmark-excel-fill me-1"></i>Descargar Excel (.xlsx)
                        </button>
                    </div>

                </div>

                @if($hayFiltros)
                    {{-- Badges resumen de filtros activos --}}
                    <div class="d-flex flex-wrap gap-1 mt-3 pt-2 border-top">
                        @foreach($nombresServicios as $ns)
                            <span class="badge bg-secondary"><i class="bi bi-search me-1"></i>{{ $ns }}</span>
                        @endforeach
                        @foreach($periodos->whereIn('id_periodo', $idPeriodos) as $p)
                            <span class="badge bg-secondary"><i class="bi bi-calendar3 me-1"></i>{{ $p->nombre }}</span>
                        @endforeach
                        @foreach($sedes->whereIn('id_sede', $idSedes) as $s)
                            <span class="badge bg-secondary"><i class="bi bi-geo-alt me-1"></i>{{ $s->nombre }}</span>
                        @endforeach
                        @foreach($areas->whereIn('id_area', $idAreas) as $a)
                            <span class="badge bg-secondary"><i class="bi bi-diagram-3-fill me-1"></i>{{ $a->nombre }}</span>
                        @endforeach
                        @foreach($componentes->whereIn('id_componente', $idComponentes) as $c)
                            <span class="badge bg-secondary"><i class="bi bi-collection-fill me-1"></i>{{ $c->nombre }}</span>
                        @endforeach
                        @foreach($lineas->whereIn('id_linea', $idLineas) as $l)
                            <span class="badge bg-secondary"><i class="bi bi-list-ul me-1"></i>{{ $l->nombre }}</span>
                        @endforeach
                        @foreach($tiposActividad->whereIn('id_tipo_actividad', $idTiposActividad) as $ta)
                            <span class="badge bg-secondary"><i class="bi bi-tags-fill me-1"></i>{{ $ta->nombre }}</span>
                        @endforeach
                        @if($fechaDesde)
                            <span class="badge bg-secondary"><i class="bi bi-calendar-event me-1"></i>Desde {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}</span>
                        @endif
                        @if($fechaHasta)
                            <span class="badge bg-secondary"><i class="bi bi-calendar-event me-1"></i>Hasta {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
                        @endif
                        @foreach($roles as $r)
                            <span class="badge" style="background-color:#196844"><i class="bi bi-person me-1"></i>{{ $r }}</span>
                        @endforeach
                        @foreach($programasDisponibles->whereIn('id_programa', $idProgramas) as $pr)
                            <span class="badge" style="background-color:#6f42c1"><i class="bi bi-journal-bookmark me-1"></i>{{ $pr->nombre }}</span>
                        @endforeach
                        @foreach($planesDisponibles->whereIn('id_plan_estudio', $idPlanes) as $pl)
                            <span class="badge" style="background-color:#6f42c1"><i class="bi bi-book me-1"></i>Plan {{ $pl->codigo_plan }}</span>
                        @endforeach
                        @foreach($tiposEmpleado as $te)
                            <span class="badge" style="background-color:#856404"><i class="bi bi-briefcase me-1"></i>{{ $te }}</span>
                        @endforeach
                        @foreach($dependencias->whereIn('id_dependencia', $idDependencias) as $dep)
                            <span class="badge" style="background-color:#856404"><i class="bi bi-diagram-3 me-1"></i>{{ $dep->nombre }}</span>
                        @endforeach
                        @foreach($cargos->whereIn('id_cargo', $idCargos) as $car)
                            <span class="badge" style="background-color:#856404"><i class="bi bi-person-badge me-1"></i>{{ $car->nombre }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    const tsOpts = (placeholder) => ({
        plugins: ['remove_button'],
        maxOptions: null,
        placeholder,
        onItemAdd() {
            this.control_input.setAttribute('placeholder', '');
        },
        onItemRemove() {
            if (!this.items.length) {
                this.control_input.setAttribute('placeholder', placeholder);
            }
        },
    });

    // ── Área → Componente → Línea (client-side cascade) ──
    const allComponentes = @json($componentes->map(fn($c) => ['id' => $c->id_componente, 'nombre' => $c->nombre, 'id_area' => $c->id_area]));
    const allLineas      = @json($lineas->map(fn($l) => ['id' => $l->id_linea, 'nombre' => $l->nombre, 'id_componente' => $l->id_componente]));

    const tsServicio   = new TomSelect('#r-servicio',      tsOpts('Todos los servicios'));
    const tsArea       = new TomSelect('#r-area',          tsOpts('Todas'));
    const tsComponente = new TomSelect('#r-componente',    tsOpts('Todos'));
    const tsLinea      = new TomSelect('#r-linea',         tsOpts('Todas'));
    const tsPeriodo    = new TomSelect('#r-periodo',       tsOpts('Todos'));
    const tsSede       = new TomSelect('#r-sede',          tsOpts('Todas'));
    const tsTipoAct    = new TomSelect('#r-tipo-actividad',tsOpts('Todos'));
    const tsFacultad   = new TomSelect('#r-facultad',      tsOpts('Todas'));
    const tsPrograma   = new TomSelect('#r-programa',      tsOpts('Todos'));
    const tsPlan       = new TomSelect('#r-plan',          tsOpts('Todos'));
    const tsDependencia= new TomSelect('#r-dependencia',   tsOpts('Todas'));
    const tsCargo      = new TomSelect('#r-cargo',         tsOpts('Todos'));

    function refreshOptions(ts, items, valueKey, labelKey) {
        const prev = ts.getValue();
        ts.clear(true);
        ts.clearOptions();
        items.forEach(i => ts.addOption({ value: String(i[valueKey]), text: i[labelKey] }));
        prev.forEach(v => { if (items.find(i => String(i[valueKey]) === v)) ts.addItem(v, true); });
        ts.refreshOptions(false);
    }

    tsArea.on('change', function () {
        const sel = Object.keys(tsArea.items).map(Number);
        const filtered = sel.length ? allComponentes.filter(c => sel.includes(c.id_area)) : allComponentes;
        refreshOptions(tsComponente, filtered, 'id', 'nombre');
        triggerComponenteChange();
    });

    function triggerComponenteChange() {
        const sel = Object.keys(tsComponente.items).map(Number);
        const filtered = sel.length ? allLineas.filter(l => sel.includes(l.id_componente)) : allLineas;
        refreshOptions(tsLinea, filtered, 'id', 'nombre');
    }
    tsComponente.on('change', triggerComponenteChange);

    // ── Facultad → Programa (AJAX) ──
    const urlProgramas = '{{ route('servicios.reportes.programas') }}';
    const urlPlanes    = '{{ route('servicios.reportes.planes') }}';

    tsFacultad.on('change', function () {
        const sel = tsFacultad.getValue();
        tsPrograma.clear(true);
        tsPrograma.clearOptions();
        tsPlan.clear(true);
        tsPlan.clearOptions();
        if (!sel.length) return;

        const params = sel.map(id => `id_facultad[]=${id}`).join('&');
        fetch(`${urlProgramas}?${params}`)
            .then(r => r.json())
            .then(data => {
                data.forEach(p => tsPrograma.addOption({ value: String(p.id), text: p.nombre }));
                @json(array_map('strval', $idProgramas)).forEach(id => tsPrograma.addItem(id, true));
                tsPrograma.refreshOptions(false);
            });
    });

    // ── Programa → Plan (AJAX) ──
    tsPrograma.on('change', function () {
        const sel = tsPrograma.getValue();
        tsPlan.clear(true);
        tsPlan.clearOptions();
        if (!sel.length) return;

        const params = sel.map(id => `id_programa[]=${id}`).join('&');
        fetch(`${urlPlanes}?${params}`)
            .then(r => r.json())
            .then(data => {
                data.forEach(p => tsPlan.addOption({ value: String(p.id), text: p.text }));
                @json(array_map('strval', $idPlanes)).forEach(id => tsPlan.addItem(id, true));
                tsPlan.refreshOptions(false);
            });
    });

    // ── Hojas disponibles (mirrors computeHojasDisponibles PHP logic) ──
    const SIEMPRE = ['resumen', 'por_servicios'];

    function computeHojasDisponibles() {
        const roles = [...document.querySelectorAll('.check-role:checked')].map(e => e.value);
        const tipos = [...document.querySelectorAll('.check-tipo-emp:checked')].map(e => e.value);
        const allRoles = roles.length === 0;
        const allTipos = tipos.length === 0;

        const available = [...SIEMPRE];
        if (allRoles || roles.includes('Estudiante'))  available.push('estudiantes');
        if (allRoles || roles.includes('Graduado'))    available.push('graduados');
        const hasEmp = allRoles || roles.includes('Empleado');
        if (hasEmp) {
            if (allTipos || tipos.includes('Administrativo')) available.push('administrativos');
            if (allTipos || tipos.includes('Contratista'))    available.push('contratistas');
            if (allTipos || tipos.includes('Docente'))        available.push('docentes');
        }
        return available;
    }

    function updateHojas() {
        const available = computeHojasDisponibles();
        document.querySelectorAll('.hoja-row').forEach(row => {
            const hoja = row.dataset.hoja;
            const cb   = row.querySelector('.check-hoja');
            const show = available.includes(hoja);
            row.style.display = show ? '' : 'none';
            cb.disabled = !show;
            if (!show) cb.checked = false;
        });
    }

    document.getElementById('btn-sel-todas').addEventListener('click', () => {
        document.querySelectorAll('.check-hoja:not(:disabled)').forEach(cb => cb.checked = true);
    });
    document.getElementById('btn-desel-todas').addEventListener('click', () => {
        document.querySelectorAll('.check-hoja').forEach(cb => {
            if (!SIEMPRE.includes(cb.value)) cb.checked = false;
        });
    });

    // ── Show / hide sub-panels por rol ──
    function updateRolePanels() {
        const checked = [...document.querySelectorAll('.check-role:checked')].map(el => el.value);
        const allUnchecked = checked.length === 0;
        const hasEst = checked.includes('Estudiante') || checked.includes('Graduado');
        const hasEmp = checked.includes('Empleado');
        document.getElementById('sub-estudiante').classList.toggle('d-none', !allUnchecked && !hasEst);
        document.getElementById('sub-empleado').classList.toggle('d-none', !allUnchecked && !hasEmp);
    }
    document.querySelectorAll('.check-role').forEach(el => el.addEventListener('change', () => {
        updateRolePanels();
        updateHojas();
    }));

    // ── Tipo empleado → actualizar hojas disponibles ──
    document.querySelectorAll('.check-tipo-emp').forEach(el => el.addEventListener('change', () => {
        updateHojas();
    }));

    // ── Init: si ya hay facultades seleccionadas, cargar programas via AJAX ──
    const facultadesPresel = @json(array_map('strval', $idFacultades));
    if (facultadesPresel.length) {
        const params = facultadesPresel.map(id => `id_facultad[]=${id}`).join('&');
        fetch(`${urlProgramas}?${params}`)
            .then(r => r.json())
            .then(data => {
                data.forEach(p => {
                    if (!tsPrograma.options[String(p.id)]) {
                        tsPrograma.addOption({ value: String(p.id), text: p.nombre });
                    }
                });
                tsPrograma.refreshOptions(false);
            });
    }

    const programasPresel = @json(array_map('strval', $idProgramas));
    if (programasPresel.length) {
        const params = programasPresel.map(id => `id_programa[]=${id}`).join('&');
        fetch(`${urlPlanes}?${params}`)
            .then(r => r.json())
            .then(data => {
                data.forEach(p => {
                    if (!tsPlan.options[String(p.id)]) {
                        tsPlan.addOption({ value: String(p.id), text: p.text });
                    }
                });
                tsPlan.refreshOptions(false);
            });
    }
})();
</script>
@endpush

</x-layout>
