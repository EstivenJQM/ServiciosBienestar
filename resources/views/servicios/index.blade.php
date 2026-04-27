<x-layout title="Servicios">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-clipboard2-heart-fill me-2" style="color:#196844"></i>Servicios
        </h2>
        <a href="{{ route('servicios.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Servicio
        </a>
    </div>

    {{-- ── Filtros ── --}}
    @php
        $nFiltros = collect([$idPeriodo, $idSede, $idArea, $idComponente, $idLinea, $idTipoActividad, $fechaDesde, $fechaHasta])
            ->filter()->count();
        $panelAbierto = $nFiltros > 0;
    @endphp

    <form method="GET" action="{{ route('servicios.index') }}" class="mb-3">

        {{-- Barra superior --}}
        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
            <div class="input-group" style="max-width:400px">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="q" value="{{ $busqueda }}"
                       class="form-control"
                       placeholder="Nombre del servicio…">
            </div>

            <button type="button"
                    class="btn btn-outline-secondary"
                    data-bs-toggle="collapse"
                    data-bs-target="#panel-filtros"
                    aria-expanded="{{ $panelAbierto ? 'true' : 'false' }}">
                <i class="bi bi-funnel me-1"></i>Filtros
                @if($nFiltros > 0)
                    <span class="badge rounded-pill ms-1" style="background:#196844">{{ $nFiltros }}</span>
                @endif
            </button>

            <button type="submit" class="btn btn-sibi">
                <i class="bi bi-search me-1"></i>Buscar
            </button>

            @if($hayFiltros)
                <a href="{{ route('servicios.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Limpiar
                </a>
            @endif
        </div>

        {{-- Panel colapsable --}}
        <div class="collapse {{ $panelAbierto ? 'show' : '' }}" id="panel-filtros">
            <div class="card card-body border rounded mb-2 py-3">
                <div class="row g-3">

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-calendar3 me-1"></i>Período
                        </label>
                        <select name="id_periodo" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach($periodos as $periodo)
                                <option value="{{ $periodo->id_periodo }}"
                                    {{ $idPeriodo == $periodo->id_periodo ? 'selected' : '' }}>
                                    {{ $periodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-geo-alt me-1"></i>Sede
                        </label>
                        <select name="id_sede" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id_sede }}"
                                    {{ $idSede == $sede->id_sede ? 'selected' : '' }}>
                                    [{{ $sede->codigo }}] {{ $sede->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-diagram-3-fill me-1"></i>Área
                        </label>
                        <select name="id_area" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id_area }}"
                                    {{ $idArea == $area->id_area ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-collection-fill me-1"></i>Componente
                        </label>
                        <select name="id_componente" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach($componentes as $comp)
                                <option value="{{ $comp->id_componente }}"
                                    {{ $idComponente == $comp->id_componente ? 'selected' : '' }}>
                                    {{ $comp->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-list-ul me-1"></i>Línea
                        </label>
                        <select name="id_linea" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            @foreach($lineas as $linea)
                                <option value="{{ $linea->id_linea }}"
                                    {{ $idLinea == $linea->id_linea ? 'selected' : '' }}>
                                    {{ $linea->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-tags-fill me-1"></i>Tipo de Actividad
                        </label>
                        <select name="id_tipo_actividad" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach($tiposActividad as $tipo)
                                <option value="{{ $tipo->id_tipo_actividad }}"
                                    {{ $idTipoActividad == $tipo->id_tipo_actividad ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Rango de fechas --}}
                    <div class="col-12">
                        <hr class="my-0">
                        <p class="small fw-semibold text-muted text-uppercase mb-2 mt-2" style="font-size:.68rem">
                            <i class="bi bi-calendar-range me-1"></i>Rango de fechas
                        </p>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            Desde
                        </label>
                        <input type="date" name="fecha_desde" value="{{ $fechaDesde }}"
                               class="form-control form-control-sm">
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            Hasta
                        </label>
                        <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}"
                               class="form-control form-control-sm">
                    </div>

                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-sibi btn-sm">
                        <i class="bi bi-funnel me-1"></i>Aplicar filtros
                    </button>
                </div>
            </div>
        </div>

    </form>

    {{-- Badges de filtros activos --}}
    @if($nFiltros > 0)
        <div class="d-flex flex-wrap gap-1 mb-2">
            @if($idPeriodo)
                @php $p = $periodos->get($idPeriodo) @endphp
                <span class="badge bg-secondary">
                    <i class="bi bi-calendar3 me-1"></i>{{ $p?->nombre ?? $idPeriodo }}
                </span>
            @endif
            @if($idSede)
                @php $s = $sedes->firstWhere('id_sede', $idSede) @endphp
                <span class="badge bg-secondary">
                    <i class="bi bi-geo-alt me-1"></i>{{ $s?->nombre ?? $idSede }}
                </span>
            @endif
            @if($idArea)
                @php $a = $areas->firstWhere('id_area', $idArea) @endphp
                <span class="badge bg-secondary">
                    <i class="bi bi-diagram-3-fill me-1"></i>{{ $a?->nombre ?? $idArea }}
                </span>
            @endif
            @if($idComponente)
                @php $c = $componentes->firstWhere('id_componente', $idComponente) @endphp
                <span class="badge bg-secondary">
                    <i class="bi bi-collection-fill me-1"></i>{{ $c?->nombre ?? $idComponente }}
                </span>
            @endif
            @if($idLinea)
                @php $l = $lineas->firstWhere('id_linea', $idLinea) @endphp
                <span class="badge bg-secondary">
                    <i class="bi bi-list-ul me-1"></i>{{ $l?->nombre ?? $idLinea }}
                </span>
            @endif
            @if($idTipoActividad)
                @php $ta = $tiposActividad->firstWhere('id_tipo_actividad', $idTipoActividad) @endphp
                <span class="badge bg-secondary">
                    <i class="bi bi-tags-fill me-1"></i>{{ $ta?->nombre ?? $idTipoActividad }}
                </span>
            @endif
            @if($fechaDesde)
                <span class="badge bg-secondary">
                    <i class="bi bi-calendar-event me-1"></i>Desde {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
                </span>
            @endif
            @if($fechaHasta)
                <span class="badge bg-secondary">
                    <i class="bi bi-calendar-event me-1"></i>Hasta {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                </span>
            @endif
        </div>
    @endif

    {{-- Contador --}}
    <p class="text-muted small mb-2">
        {{ number_format($total) }} {{ $total === 1 ? 'servicio encontrado' : 'servicios encontrados' }}
    </p>

    @if($servicios->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-4">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                No hay servicios{{ $busqueda !== '' ? ' para «'.$busqueda.'»' : '' }}.
            </p>
        </x-card>
    @else
        @php
            $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        @endphp

        @foreach($servicios as $idPeriodoKey => $lista)
            @php
                $periodoItem = $periodos->get($idPeriodoKey);
                $partes      = explode('-', $periodoItem?->nombre ?? '');
                $esPrimero   = ($partes[1] ?? '1') === '1';
            @endphp

            {{-- Encabezado de período --}}
            <div class="d-flex align-items-center justify-content-between mt-4 mb-2 px-1">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold fs-5" style="color:#196844">
                        <i class="bi bi-calendar3 me-1"></i>{{ $periodoItem?->nombre ?? '—' }}
                    </span>
                    <span class="badge"
                          style="background-color:{{ $esPrimero ? '#196844' : '#ffd400' }};color:{{ $esPrimero ? '#fff' : '#000' }};font-size:.72rem">
                        {{ $esPrimero ? 'Primer semestre' : 'Segundo semestre' }}
                    </span>
                </div>
                <span class="badge bg-light text-dark border" style="font-size:.75rem">
                    {{ $lista->count() }} {{ $lista->count() === 1 ? 'servicio' : 'servicios' }}
                </span>
            </div>

            <x-card :padding="false">
                <table class="table table-hover table-sm mb-0 align-middle" style="table-layout:fixed;width:100%">
                    <colgroup>
                        <col style="width:55px">
                        <col style="width:18%">
                        <col style="width:38%">
                        <col style="width:24%">
                        <col style="width:80px">
                        <col style="width:110px">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Línea / Tipo Actividad</th>
                            <th class="text-center">Sede</th>
                            <th class="text-center">Usuarios</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lista as $servicio)
                            @php
                                $mes = $meses[$servicio->fecha->month - 1];
                            @endphp
                            <tr>
                                {{-- Mini calendario --}}
                                <td class="text-center px-2">
                                    <div style="width:38px;border:1px solid #dee2e6;border-radius:.375rem;overflow:hidden;font-size:.75rem;line-height:1">
                                        <div style="background:#196844;color:#fff;padding:2px 0;font-size:.6rem;letter-spacing:.03em">
                                            {{ $mes }}
                                        </div>
                                        <div class="fw-bold py-1" style="font-size:1rem">
                                            {{ $servicio->fecha->format('d') }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Nombre --}}
                                <td class="text-center">
                                    <a href="{{ route('servicios.show', $servicio) }}"
                                       class="fw-semibold text-decoration-none"
                                       style="color:#196844">
                                        {{ $servicio->nombre }}
                                    </a>
                                </td>

                                {{-- Línea + Tipo de actividad --}}
                                <td class="text-center">
                                    <div class="d-flex flex-wrap gap-1 align-items-center justify-content-center">
                                        <span class="badge" style="background-color:#20c997;font-size:.68rem">
                                            {{ $servicio->linea->nombre }}
                                        </span>
                                        <span class="badge" style="background-color:#fd7e14;font-size:.68rem">
                                            {{ $servicio->tipoActividad->nombre }}
                                        </span>
                                    </div>
                                    <small class="text-muted" style="font-size:.7rem">
                                        {{ $servicio->linea->componente->area->nombre }}
                                        <i class="bi bi-chevron-right" style="font-size:.55rem"></i>
                                        {{ $servicio->linea->componente->nombre }}
                                    </small>
                                </td>

                                {{-- Sede --}}
                                <td class="text-center">
                                    <span class="badge bg-secondary" style="font-size:.68rem">
                                        {{ $servicio->sede->codigo }}
                                    </span>
                                    <span class="small text-muted">{{ $servicio->sede->nombre }}</span>
                                </td>

                                {{-- Usuarios asignados --}}
                                <td class="text-center">
                                    @if($servicio->usuarios_asignados_count > 0)
                                        <span class="badge rounded-pill"
                                              style="background-color:#e6f2ec;color:#196844;font-size:.75rem;border:1px solid #196844">
                                            <i class="bi bi-people-fill me-1"></i>{{ $servicio->usuarios_asignados_count }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="text-end">
                                    <div class="d-flex flex-column gap-1 align-items-end">
                                        <a href="{{ route('servicios.show', $servicio) }}"
                                           class="btn btn-sm btn-sibi">
                                            <i class="bi bi-people-fill me-1"></i>Asignar
                                        </a>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('servicios.edit', $servicio) }}"
                                               class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <form action="{{ route('servicios.destroy', $servicio) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar el servicio «{{ $servicio->nombre }}»?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-card>
        @endforeach
    @endif

</x-layout>
