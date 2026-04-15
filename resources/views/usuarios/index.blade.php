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
                $urs = $usuario->rolesEnSedes->sortByDesc(fn($r) => $r->id_periodo);
            @endphp
            <div class="card shadow-sm mb-2">
                <div class="card-body py-2 px-3">

                    {{-- Fila de identidad --}}
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-semibold">{{ $usuario->nombre_completo }}</span>
                            <span class="badge bg-secondary" style="font-size:.7rem">
                                <i class="bi bi-person-vcard me-1"></i>{{ $usuario->documento }}
                            </span>
                            <span class="text-muted small">
                                <i class="bi bi-envelope me-1"></i>{{ $usuario->correo }}
                            </span>
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

                    {{-- Roles en sedes --}}
                    @if($urs->isNotEmpty())
                        <div class="d-flex flex-column gap-1 mt-1">
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
                                @endphp
                                <div class="d-flex align-items-start flex-wrap gap-1"
                                     style="padding:4px 6px; background:{{ $rolBg }}; border-radius:.375rem; border-left: 3px solid {{ $rolColor }}">

                                    {{-- Rol --}}
                                    <span class="badge" style="background-color:{{ $rolColor }};font-size:.72rem">
                                        {{ $entry->rol?->nombre ?? '—' }}
                                    </span>

                                    {{-- Sede --}}
                                    @if($entry->sede)
                                        <span class="badge bg-white text-dark border" style="font-size:.7rem">
                                            <span class="badge bg-secondary me-1" style="font-size:.62rem">{{ $entry->sede->codigo }}</span>{{ $entry->sede->nombre }}
                                        </span>
                                    @endif

                                    {{-- Período --}}
                                    @if($entry->periodo)
                                        <span class="badge bg-light text-dark border" style="font-size:.7rem">
                                            <i class="bi bi-calendar3 me-1"></i>{{ $entry->periodo->nombre }}
                                        </span>
                                    @endif

                                    {{-- Estado --}}
                                    <span class="badge {{ $entry->estado === 'activo' ? 'bg-success' : 'bg-danger' }}"
                                          style="font-size:.68rem">
                                        {{ ucfirst($entry->estado) }}
                                    </span>

                                    {{-- Info académica del estudiante (sólo en modo estudiante) --}}
                                    @if($soloEstudiantes && $esEstudiante && $entry->estudianteEgresado)
                                        @php
                                            $plan      = $entry->estudianteEgresado->planEstudio;
                                            $progSede  = $plan?->programaSede;
                                            $programa  = $progSede?->programa;
                                            $facultad  = $programa?->facultad;
                                        @endphp
                                        <span class="vr mx-1 align-self-center"></span>

                                        @if($plan)
                                            <span class="badge bg-info text-dark" style="font-size:.7rem">
                                                <i class="bi bi-book me-1"></i>Plan {{ $plan->codigo_plan }}
                                            </span>
                                        @endif

                                        @if($programa)
                                            <span class="badge bg-white text-dark border" style="font-size:.7rem">
                                                <i class="bi bi-journal-bookmark me-1"></i>{{ $programa->nombre }}
                                            </span>
                                        @endif

                                        @if($facultad)
                                            <span class="badge" style="background-color:#6f42c1;font-size:.7rem">
                                                <i class="bi bi-building me-1"></i>{{ $facultad->nombre }}
                                            </span>
                                        @endif
                                    @endif

                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted small">Sin roles asignados</span>
                    @endif

                </div>
            </div>
        @endforeach

        {{-- Paginación --}}
        <div class="mt-3">
            {{ $usuarios->links() }}
        </div>
    @endif

</x-layout>
