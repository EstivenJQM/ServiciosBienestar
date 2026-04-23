<x-layout title="Usuarios">

    {{-- ── Cabecera ── --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">
            <i class="bi bi-people-fill me-2" style="color:#196844"></i>Usuarios
        </h2>
    </div>

    {{-- ── Filtros ── --}}
    @php
        $filtrosActivos = array_filter(compact('idSede','idRol','idPeriodo','estado','busqueda'));
        $hayFiltros     = count($filtrosActivos) > 0;
    @endphp

    <form method="GET" action="{{ route('usuarios.index') }}" class="mb-3">

        {{-- Fila 1: búsqueda + botón limpiar --}}
        <div class="d-flex flex-wrap gap-2 align-items-end mb-2">
            <div class="input-group" style="max-width:400px">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="q" value="{{ $busqueda }}"
                       class="form-control"
                       placeholder="Nombre, documento o correo…">
            </div>

            <button type="submit" class="btn btn-sibi">
                <i class="bi bi-funnel me-1"></i>Filtrar
            </button>

            @if($hayFiltros)
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Limpiar filtros
                </a>
            @endif
        </div>

        {{-- Fila 2: selects de filtro --}}
        <div class="d-flex flex-wrap gap-2">

            <select name="id_sede" class="form-select form-select-sm" style="max-width:200px">
                <option value="">— Todas las sedes —</option>
                @foreach($sedes as $sede)
                    <option value="{{ $sede->id_sede }}"
                        {{ $idSede == $sede->id_sede ? 'selected' : '' }}>
                        [{{ $sede->codigo }}] {{ $sede->nombre }}
                    </option>
                @endforeach
            </select>

            <select name="id_rol" class="form-select form-select-sm" style="max-width:170px">
                <option value="">— Todos los roles —</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol->id_rol }}"
                        {{ $idRol == $rol->id_rol ? 'selected' : '' }}>
                        {{ $rol->nombre }}
                    </option>
                @endforeach
            </select>

            <select name="id_periodo" class="form-select form-select-sm" style="max-width:160px">
                <option value="">— Todos los períodos —</option>
                @foreach($periodos as $periodo)
                    <option value="{{ $periodo->id_periodo }}"
                        {{ $idPeriodo == $periodo->id_periodo ? 'selected' : '' }}>
                        {{ $periodo->nombre }}
                    </option>
                @endforeach
            </select>

            <select name="estado" class="form-select form-select-sm" style="max-width:150px">
                <option value="">— Cualquier estado —</option>
                <option value="activo"   {{ $estado === 'activo'   ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ $estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>

        </div>
    </form>

    {{-- Badges de filtros activos --}}
    @if($hayFiltros)
        <div class="d-flex flex-wrap gap-1 mb-2">
            @if($busqueda)
                <span class="badge bg-secondary">
                    <i class="bi bi-search me-1"></i>«{{ $busqueda }}»
                </span>
            @endif
            @if($idSede)
                @php $s = $sedes->firstWhere('id_sede', $idSede) @endphp
                <span class="badge bg-secondary">
                    <i class="bi bi-geo-alt me-1"></i>{{ $s?->nombre ?? $idSede }}
                </span>
            @endif
            @if($idRol)
                @php $r = $roles->firstWhere('id_rol', $idRol) @endphp
                <span class="badge bg-secondary">
                    <i class="bi bi-person-badge me-1"></i>{{ $r?->nombre ?? $idRol }}
                </span>
            @endif
            @if($idPeriodo)
                @php $p = $periodos->firstWhere('id_periodo', $idPeriodo) @endphp
                <span class="badge bg-secondary">
                    <i class="bi bi-calendar3 me-1"></i>{{ $p?->nombre ?? $idPeriodo }}
                </span>
            @endif
            @if($estado)
                <span class="badge {{ $estado === 'activo' ? 'bg-success' : 'bg-danger' }}">
                    {{ ucfirst($estado) }}
                </span>
            @endif
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
                                        'Estudiante' => '#196844',
                                        'Graduado'   => '#0d6efd',
                                        'Empleado'   => match($tipoEmpNombre) {
                                            'Contratista'    => '#6f42c1',
                                            'Administrativo' => '#0d6efd',
                                            'Docente'        => '#856404',
                                            default          => '#856404',
                                        },
                                        default => '#6c757d',
                                    };
                                    $rolBg = match($entry->rol?->nombre) {
                                        'Estudiante' => '#e6f2ec',
                                        'Graduado'   => '#e7f0ff',
                                        'Empleado'   => match($tipoEmpNombre) {
                                            'Contratista'    => '#f3eeff',
                                            'Administrativo' => '#e7f0ff',
                                            'Docente'        => '#fff8e1',
                                            default          => '#fff8e1',
                                        },
                                        default => '#f3f4f6',
                                    };
                                    $bs = 'd-inline-flex align-items-center px-2 py-0 gap-1';
                                    $bh = 'height:1.6rem;font-size:.7rem';
                                @endphp
                                <div class="d-flex align-items-center flex-wrap gap-1"
                                     style="padding:4px 6px; background:{{ $rolBg }}; border-radius:.375rem; border-left: 3px solid {{ $rolColor }}">

                                    <span class="badge {{ $bs }}" style="{{ $bh }};background-color:{{ $rolColor }}">
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
                                            <span class="badge bg-info text-dark {{ $bs }}" style="{{ $bh }}">
                                                <i class="bi bi-book"></i>Plan {{ $plan->codigo_plan }}
                                            </span>
                                        @endif
                                        @if($programa)
                                            <span class="badge bg-white text-dark border {{ $bs }}" style="{{ $bh }}">
                                                <i class="bi bi-journal-bookmark"></i>{{ $programa->nombre }}
                                            </span>
                                        @endif
                                        @if($facultad)
                                            <span class="badge {{ $bs }}" style="{{ $bh }};background-color:#6f42c1">
                                                <i class="bi bi-building"></i>{{ $facultad->nombre }}
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
                                            <span class="badge {{ $bs }}" style="{{ $bh }};background-color:#fd7e14">
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
