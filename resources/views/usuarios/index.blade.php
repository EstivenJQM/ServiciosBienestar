<x-layout title="Usuarios">

    {{-- ── Cabecera ── --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">
            <i class="bi bi-people-fill me-2" style="color:#196844"></i>Usuarios
        </h2>
        <a href="{{ route('usuarios.index', array_merge(request()->except(['solo_estudiantes','page']), ['solo_estudiantes' => $soloEstudiantes ? 0 : 1])) }}"
           class="btn {{ $soloEstudiantes ? 'btn-sibi' : 'btn-outline-sibi' }}">
            <i class="bi bi-mortarboard{{ $soloEstudiantes ? '-fill' : '' }} me-1"></i>
            {{ $soloEstudiantes ? 'Mostrando estudiantes — Ver todos' : 'Mostrar estudiantes' }}
        </a>
    </div>

    {{-- ── Buscador ── --}}
    <form method="GET" action="{{ route('usuarios.index') }}" class="mb-3">
        @if($soloEstudiantes)
            <input type="hidden" name="solo_estudiantes" value="1">
        @endif
        <div class="input-group" style="max-width:460px">
            <span class="input-group-text bg-white">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" name="q" value="{{ $busqueda }}"
                   class="form-control"
                   placeholder="Buscar por nombre, documento o correo…">
            @if($busqueda)
                <a href="{{ route('usuarios.index', $soloEstudiantes ? ['solo_estudiantes'=>1] : []) }}"
                   class="btn btn-outline-secondary" title="Limpiar búsqueda">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
            <button type="submit" class="btn btn-sibi">Buscar</button>
        </div>
    </form>

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
            @if($soloEstudiantes) — filtro: <strong>Estudiantes</strong> @endif
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
                           class="btn btn-sm btn-warning" title="Editar">
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
                                    $esEstudiante = $entry->rol?->nombre === 'Estudiante';
                                    $rolColor = match($entry->rol?->nombre) {
                                        'Estudiante' => '#196844',
                                        'Graduado'   => '#0d6efd',
                                        'Empleado'   => '#856404',
                                        default      => '#6c757d',
                                    };
                                    $rolBg = match($entry->rol?->nombre) {
                                        'Estudiante' => '#e6f2ec',
                                        'Graduado'   => '#e7f0ff',
                                        'Empleado'   => '#fff8e1',
                                        default      => '#f3f4f6',
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

                                    @if($soloEstudiantes && $esEstudiante && $entry->estudianteEgresado)
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
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(btn => {
            const collapseEl = document.querySelector(btn.getAttribute('data-bs-target'));
            if (!collapseEl) return;
            const icon = btn.querySelector('.toggle-icon');
            if (!icon) return;
            collapseEl.addEventListener('show.bs.collapse', () => icon.style.transform = 'rotate(90deg)');
            collapseEl.addEventListener('hide.bs.collapse', () => icon.style.transform = 'rotate(0deg)');
        });
    });
</script>

</x-layout>
