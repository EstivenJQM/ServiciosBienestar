<x-layout title="Usuarios">

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

    {{-- ── Cabecera ── --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">
            <i class="bi bi-people-fill me-2" style="color:#196844"></i>Usuarios
        </h2>
    </div>

    {{-- ── Filtros ── --}}
    @php
        $nFiltros = (int)(!empty($idSedes)) + (int)(!empty($idRoles)) + (int)(!empty($idPeriodos))
                  + (int)($estado !== '' && $estado !== null)
                  + (int)(!empty($idTiposEmpleado)) + (int)(!empty($idDependencias)) + (int)(!empty($idCargos))
                  + (int)(!empty($idFacultades))    + (int)(!empty($idProgramas));
        $hayFiltros   = $nFiltros > 0 || $busqueda !== '';
        $panelAbierto = $nFiltros > 0;
    @endphp

    <form method="GET" action="{{ route('usuarios.index') }}" class="mb-3">

        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
            <div class="input-group" style="max-width:400px">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="q" value="{{ $busqueda }}"
                       class="form-control" placeholder="Nombre, documento o correo…">
            </div>

            <button type="button" class="btn btn-outline-secondary"
                    data-bs-toggle="collapse" data-bs-target="#panel-filtros"
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
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Limpiar
                </a>
            @endif
        </div>

        <div class="collapse {{ $panelAbierto ? 'show' : '' }}" id="panel-filtros">
            <div class="card card-body border rounded mb-2 py-3">
                <div class="row g-3">

                    {{-- ── General ── --}}
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-geo-alt me-1"></i>Sede
                        </label>
                        <select name="id_sede[]" multiple class="form-select form-select-sm ts-filter" data-placeholder="Todas">
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id_sede }}" {{ in_array($sede->id_sede, $idSedes) ? 'selected' : '' }}>
                                    [{{ $sede->codigo }}] {{ $sede->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-person-badge me-1"></i>Rol
                        </label>
                        <select name="id_rol[]" multiple class="form-select form-select-sm ts-filter" data-placeholder="Todos">
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id_rol }}" {{ in_array($rol->id_rol, $idRoles) ? 'selected' : '' }}>
                                    {{ $rol->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-calendar3 me-1"></i>Período
                        </label>
                        <select name="id_periodo[]" multiple class="form-select form-select-sm ts-filter" data-placeholder="Todos">
                            @foreach($periodos as $periodo)
                                <option value="{{ $periodo->id_periodo }}" {{ in_array($periodo->id_periodo, $idPeriodos) ? 'selected' : '' }}>
                                    {{ $periodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-circle-half me-1"></i>Estado
                        </label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Cualquiera</option>
                            <option value="activo"   {{ $estado === 'activo'   ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ $estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    {{-- ── Académico ── --}}
                    <div class="col-12">
                        <hr class="my-0">
                        <p class="small fw-semibold text-muted text-uppercase mb-0 mt-2" style="font-size:.68rem">
                            <i class="bi bi-mortarboard-fill me-1"></i>Académico
                        </p>
                    </div>

                    <div class="col-sm-6 col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-building me-1"></i>Facultad
                        </label>
                        <select name="id_facultad[]" multiple class="form-select form-select-sm ts-filter" data-placeholder="Todas">
                            @foreach($facultades as $fac)
                                <option value="{{ $fac->id_facultad }}" {{ in_array($fac->id_facultad, $idFacultades) ? 'selected' : '' }}>
                                    {{ $fac->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-8">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-journal-bookmark-fill me-1"></i>Programa
                        </label>
                        <select name="id_programa[]" multiple class="form-select form-select-sm ts-filter" data-placeholder="Todos">
                            @foreach($programas as $prog)
                                <option value="{{ $prog->id_programa }}" {{ in_array($prog->id_programa, $idProgramas) ? 'selected' : '' }}>
                                    {{ $prog->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ── Empleado ── --}}
                    <div class="col-12">
                        <hr class="my-0">
                        <p class="small fw-semibold text-muted text-uppercase mb-0 mt-2" style="font-size:.68rem">
                            <i class="bi bi-briefcase-fill me-1"></i>Empleado
                        </p>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            Tipo
                        </label>
                        <select name="id_tipo_empleado[]" multiple class="form-select form-select-sm ts-filter" data-placeholder="Todos">
                            @foreach($tiposEmpleado as $tipo)
                                <option value="{{ $tipo->id_tipo_empleado }}" {{ in_array($tipo->id_tipo_empleado, $idTiposEmpleado) ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-5">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-diagram-3 me-1"></i>Dependencia
                        </label>
                        <select name="id_dependencia[]" multiple class="form-select form-select-sm ts-filter" data-placeholder="Todas">
                            @foreach($dependencias as $dep)
                                <option value="{{ $dep->id_dependencia }}" {{ in_array($dep->id_dependencia, $idDependencias) ? 'selected' : '' }}>
                                    {{ $dep->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size:.68rem">
                            <i class="bi bi-person-badge me-1"></i>Cargo
                        </label>
                        <select name="id_cargo[]" multiple class="form-select form-select-sm ts-filter" data-placeholder="Todos">
                            @foreach($cargos as $cargo)
                                <option value="{{ $cargo->id_cargo }}" {{ in_array($cargo->id_cargo, $idCargos) ? 'selected' : '' }}>
                                    {{ $cargo->nombre }}
                                </option>
                            @endforeach
                        </select>
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
            @foreach($sedes->whereIn('id_sede', $idSedes) as $s)
                <span class="badge bg-secondary"><i class="bi bi-geo-alt me-1"></i>{{ $s->nombre }}</span>
            @endforeach
            @foreach($roles->whereIn('id_rol', $idRoles) as $r)
                <span class="badge bg-secondary"><i class="bi bi-person-badge me-1"></i>{{ $r->nombre }}</span>
            @endforeach
            @foreach($periodos->whereIn('id_periodo', $idPeriodos) as $p)
                <span class="badge bg-secondary"><i class="bi bi-calendar3 me-1"></i>{{ $p->nombre }}</span>
            @endforeach
            @if($estado)
                <span class="badge {{ $estado === 'activo' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($estado) }}</span>
            @endif
            @foreach($facultades->whereIn('id_facultad', $idFacultades) as $fac)
                <span class="badge bg-secondary"><i class="bi bi-building me-1"></i>{{ $fac->nombre }}</span>
            @endforeach
            @foreach($programas->whereIn('id_programa', $idProgramas) as $prog)
                <span class="badge bg-secondary"><i class="bi bi-journal-bookmark me-1"></i>{{ $prog->nombre }}</span>
            @endforeach
            @foreach($tiposEmpleado->whereIn('id_tipo_empleado', $idTiposEmpleado) as $te)
                <span class="badge bg-secondary"><i class="bi bi-briefcase me-1"></i>{{ $te->nombre }}</span>
            @endforeach
            @foreach($dependencias->whereIn('id_dependencia', $idDependencias) as $dep)
                <span class="badge bg-secondary"><i class="bi bi-diagram-3 me-1"></i>{{ $dep->nombre }}</span>
            @endforeach
            @foreach($cargos->whereIn('id_cargo', $idCargos) as $car)
                <span class="badge bg-secondary"><i class="bi bi-person-badge me-1"></i>{{ $car->nombre }}</span>
            @endforeach
        </div>
    @endif

    {{-- ── Resultados ── --}}
    @if($usuarios->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No se encontraron usuarios{{ $busqueda ? ' para «'.$busqueda.'»' : '' }}.
            </p>
        </x-card>
    @else
        <p class="text-muted small mb-2">
            {{ number_format($usuarios->total()) }} usuario(s) encontrado(s)
        </p>

        @foreach($usuarios as $usuario)
            @php
                $urs        = $usuario->rolesEnSedes->sortByDesc(fn($r) => $r->id_periodo);
                $collapseId = 'usr-' . $usuario->id_usuario;
            @endphp

            <div class="tree-area rounded p-3 mb-2 bg-white shadow-sm">

                {{-- Fila principal --}}
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap">
                        <button class="btn btn-link text-start fw-bold p-0 text-decoration-none text-dark d-flex align-items-center gap-1"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}"
                                aria-expanded="false">
                            <i class="bi bi-chevron-right toggle-icon" style="font-size:.75rem;transition:transform .2s"></i>
                            {{ $usuario->nombre_completo }}
                        </button>
                        <span class="badge bg-secondary" style="font-size:.7rem">
                            <i class="bi bi-person-vcard me-1"></i>{{ $usuario->documento }}
                        </span>
                        @if($usuario->correo)
                            <span class="text-muted small">
                                <i class="bi bi-envelope me-1"></i>{{ $usuario->correo }}
                            </span>
                        @endif
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <a href="{{ route('usuarios.edit', $usuario) }}"
                           class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST"
                              class="d-inline"
                              onsubmit="return confirm('¿Eliminar al usuario {{ $usuario->nombre_completo }}?\nSe eliminarán también todos sus roles y registros asociados.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Detalle colapsable --}}
                <div class="collapse mt-2 ms-3" id="{{ $collapseId }}">
                    @if($urs->isNotEmpty())
                        <div class="d-flex flex-column gap-1">
                            @foreach($urs as $entry)
                                @php
                                    $esEstudiante = in_array($entry->rol?->nombre, ['Estudiante', 'Graduado']);
                                    $tipoEmpNombre = $entry->empleado?->tipoEmpleado?->nombre;
                                    $rolColor = match($entry->rol?->nombre) {
                                        'Estudiante' => '#2E7D32',
                                        'Graduado'   => '#66BB6A',
                                        'Familiar'   => '#7B1FA2',
                                        'Empleado'   => match($tipoEmpNombre) {
                                            'Administrativo' => '#42A5F5',
                                            'Contratista'    => '#90CAF9',
                                            'Docente'        => '#EF6C00',
                                            'Planta'         => '#FF9800',
                                            'Ocasional'      => '#FFB74D',
                                            'Cátedra'        => '#FFE0B2',
                                            default          => '#1565C0',
                                        },
                                        default => '#6c757d',
                                    };
                                    $rolBg = match($entry->rol?->nombre) {
                                        'Estudiante' => '#e8f5e9',
                                        'Graduado'   => '#f1f8f1',
                                        'Familiar'   => '#f3e5f5',
                                        'Empleado'   => match($tipoEmpNombre) {
                                            'Administrativo' => '#e3f2fd',
                                            'Contratista'    => '#e8f4fd',
                                            'Docente'        => '#fff3e0',
                                            'Planta'         => '#fff3e0',
                                            'Ocasional'      => '#fff8ee',
                                            'Cátedra'        => '#fffaf5',
                                            default          => '#e3f2fd',
                                        },
                                        default => '#f3f4f6',
                                    };
                                    $rolTextColor = in_array($rolColor, ['#66BB6A','#42A5F5','#90CAF9','#FF9800','#FFB74D','#FFE0B2']) ? '#000' : '#fff';
                                    $bs = 'd-inline-flex align-items-center px-2 py-0 gap-1';
                                    $bh = 'height:1.6rem;font-size:.7rem';
                                @endphp
                                <div class="d-flex align-items-center flex-wrap gap-1"
                                     style="padding:4px 6px; background:{{ $rolBg }}; border-radius:.375rem; border-left: 3px solid {{ $rolColor }}">

                                    <span class="badge {{ $bs }}" style="{{ $bh }};background-color:{{ $rolColor }};color:{{ $rolTextColor }}">
                                        {{ $entry->rol?->nombre ?? '—' }}
                                    </span>

                                    @if($entry->sede)
                                        <span class="badge bg-white text-dark border {{ $bs }}" style="{{ $bh }}">
                                            <span class="badge bg-secondary" style="font-size:.62rem">{{ $entry->sede->codigo }}</span>{{ $entry->sede->nombre }}
                                        </span>
                                    @endif

                                    @if($entry->periodo)
                                        <span class="badge bg-light text-dark border {{ $bs }}" style="{{ $bh }}">
                                            <i class="bi bi-calendar3"></i>{{ $entry->periodo->nombre }}
                                        </span>
                                    @endif

                                    <span class="badge {{ $bs }} {{ $entry->estado === 'activo' ? 'bg-success' : 'bg-danger' }}"
                                          style="{{ $bh }}">
                                        {{ ucfirst($entry->estado) }}
                                    </span>

                                    @if($esEstudiante && $entry->estudianteEgresado)
                                        @php
                                            $plan     = $entry->estudianteEgresado->planEstudio;
                                            $progSede = $plan?->programaSede;
                                            $programa = $progSede?->programa;
                                            $facultad = $programa?->facultad;
                                        @endphp
                                        <span class="vr mx-1 align-self-center"></span>
                                        @if($plan)
                                            <span class="badge bg-white text-dark border {{ $bs }}" style="{{ $bh }}">
                                                <i class="bi bi-book"></i>Plan {{ $plan->codigo_plan }}
                                            </span>
                                        @endif
                                        @if($programa)
                                            <span class="badge bg-white text-dark border {{ $bs }}" style="{{ $bh }}">
                                                <i class="bi bi-journal-bookmark"></i>{{ $programa->nombre }}
                                            </span>
                                        @endif
                                        @if($facultad)
                                            <span class="badge bg-white text-dark border {{ $bs }}" style="{{ $bh }}">
                                                <i class="bi bi-building" ></i>{{ $facultad->nombre }}
                                            </span>
                                        @endif
                                    @endif

                                    @if($entry->rol?->nombre === 'Empleado' && $entry->empleado)
                                        @php
                                            $tipoEmp   = $entry->empleado->tipoEmpleado;
                                            $depNombre = $entry->empleado->dependencia?->nombre;
                                            $cargoNom  = $entry->empleado->cargo?->nombre;
                                        @endphp
                                        <span class="vr mx-1 align-self-center"></span>
                                        @if($tipoEmp)
                                            @php
                                                $teColor = match($tipoEmp->nombre) {
                                                    'Administrativo' => '#42A5F5',
                                                    'Contratista'    => '#90CAF9',
                                                    'Docente'        => '#EF6C00',
                                                    'Planta'         => '#FF9800',
                                                    'Ocasional'      => '#FFB74D',
                                                    'Cátedra'        => '#FFE0B2',
                                                    default          => '#1565C0',
                                                };
                                                $teText = in_array($tipoEmp->nombre, ['Administrativo','Contratista','Planta','Ocasional','Cátedra']) ? '#000' : '#fff';
                                            @endphp
                                            <span class="badge {{ $bs }}" style="{{ $bh }};background-color:{{ $teColor }};color:{{ $teText }}">
                                                <i class="bi bi-briefcase"></i>{{ $tipoEmp->nombre }}
                                            </span>
                                        @endif
                                        @if($depNombre)
                                            <span class="badge bg-white text-dark border {{ $bs }}" style="{{ $bh }}">
                                                <i class="bi bi-diagram-3"></i>{{ $depNombre }}
                                            </span>
                                        @endif
                                        @if($cargoNom)
                                            <span class="badge bg-white text-dark border {{ $bs }}" style="{{ $bh }}">
                                                <i class="bi bi-person-badge"></i>{{ $cargoNom }}
                                            </span>
                                        @endif
                                    @endif

                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted small">Sin roles asignados.</span>
                    @endif
                </div>

            </div>
        @endforeach

        {{-- Paginación --}}
        <div class="mt-3">
            {{ $usuarios->links() }}
        </div>
    @endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.querySelectorAll('.ts-filter').forEach(el => {
        new TomSelect(el, {
            plugins: ['remove_button'],
            maxOptions: null,
            placeholder: el.dataset.placeholder || 'Seleccionar…',
        });
    });
</script>
@endpush

<script>
    const STORAGE_KEY = 'usuarios_open';

    document.addEventListener('DOMContentLoaded', () => {
        const abiertos = new Set(JSON.parse(sessionStorage.getItem(STORAGE_KEY) ?? '[]'));

        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(btn => {
            const target     = btn.getAttribute('data-bs-target');
            const collapseEl = document.querySelector(target);
            if (!collapseEl) return;
            const icon = btn.querySelector('.toggle-icon');

            // Restaurar estado abierto si estaba guardado
            if (abiertos.has(target)) {
                bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
            }

            collapseEl.addEventListener('show.bs.collapse', () => {
                if (icon) icon.style.transform = 'rotate(90deg)';
                abiertos.add(target);
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify([...abiertos]));
            });

            collapseEl.addEventListener('hide.bs.collapse', () => {
                if (icon) icon.style.transform = 'rotate(0deg)';
                abiertos.delete(target);
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify([...abiertos]));
            });
        });
    });
</script>

</x-layout>
