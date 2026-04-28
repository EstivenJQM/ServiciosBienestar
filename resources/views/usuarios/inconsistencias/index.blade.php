<x-layout title="Inconsistencias de Carga">

    {{-- ── Cabecera ── --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">
            <i class="bi bi-exclamation-triangle-fill me-2" style="color:#dc3545"></i>
            Inconsistencias de Carga
            @if($total > 0)
                <span class="badge bg-danger ms-1" style="font-size:.65rem;vertical-align:middle">
                    {{ $total }}
                </span>
            @endif
        </h2>
    </div>

    {{-- ── Filtro por período + limpiar todo ── --}}
    <div class="d-flex flex-wrap gap-2 align-items-end mb-3">

        <form method="GET" action="{{ route('usuarios.inconsistencias.index') }}"
              class="d-flex align-items-end gap-2">
            <div>
                <label class="form-label small fw-semibold mb-1">Filtrar por período</label>
                <select name="id_periodo" class="form-select form-select-sm" style="min-width:160px"
                        onchange="this.form.submit()">
                    <option value="">— Todos —</option>
                    @foreach($periodos as $p)
                        <option value="{{ $p->id_periodo }}"
                            {{ $idPeriodo == $p->id_periodo ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($idPeriodo)
                <a href="{{ route('usuarios.inconsistencias.index') }}"
                   class="btn btn-sm btn-outline-secondary" title="Quitar filtro">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>

        @if($inconsistencias->total() > 0)
            <form method="POST"
                  action="{{ route('usuarios.inconsistencias.destroy-all') }}"
                  onsubmit="return confirm('¿Eliminar {{ $inconsistencias->total() }} inconsistencia(s){{ $idPeriodo ? ' del período seleccionado' : '' }}?\nEsta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                @if($idPeriodo)
                    <input type="hidden" name="id_periodo" value="{{ $idPeriodo }}">
                @endif
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash-fill me-1"></i>
                    Eliminar {{ $idPeriodo ? 'del período' : 'todas' }}
                    ({{ $inconsistencias->total() }})
                </button>
            </form>
        @endif

    </div>

    {{-- ── Contenido ── --}}
    @if($inconsistencias->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-4">
                <i class="bi bi-check-circle-fill fs-3 d-block mb-2 text-success"></i>
                No hay inconsistencias{{ $idPeriodo ? ' para el período seleccionado' : '' }}.
            </p>
        </x-card>
    @else
        <p class="text-muted small mb-2">
            {{ number_format($inconsistencias->total()) }} inconsistencia(s) encontrada(s)
        </p>

        <div class="card shadow-sm" style="overflow:hidden">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:55px" class="text-center">Fila</th>
                            <th>Período</th>
                            <th>Rol</th>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Sede</th>
                            <th>Error detectado</th>
                            <th style="width:90px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inconsistencias as $inc)
                            @php
                                $campo = $inc->campoError();
                                $badgeColor = match($campo) {
                                    'codigo_sede'     => 'bg-danger',
                                    'nombre_facultad' => 'bg-warning text-dark',
                                    'nombre_programa' => 'bg-info text-dark',
                                    'codigo_plan'     => 'bg-secondary',
                                    default           => 'bg-dark',
                                };
                                $campoLabel = match($campo) {
                                    'codigo_sede'     => 'Sede',
                                    'nombre_facultad' => 'Facultad',
                                    'nombre_programa' => 'Programa',
                                    'codigo_plan'     => 'Plan',
                                    'documento'       => 'Documento',
                                    'email'           => 'Email',
                                    default           => 'General',
                                };
                            @endphp
                            @php
                                $rolBadge = match($inc->nombre_rol ?? 'Estudiante') {
                                    'Graduado'       => ['#66BB6A', '#000', 'bi-mortarboard-fill'],
                                    'Familiar'       => ['#7B1FA2', '#fff', 'bi-house-heart-fill'],
                                    'Empleado'       => ['#1565C0', '#fff', 'bi-person-badge-fill'],
                                    'Docente'        => ['#EF6C00', '#fff', 'bi-person-badge-fill'],
                                    'Administrativo' => ['#42A5F5', '#000', 'bi-person-badge-fill'],
                                    'Contratista'    => ['#90CAF9', '#000', 'bi-person-badge-fill'],
                                    'Planta'         => ['#FF9800', '#000', 'bi-person-badge-fill'],
                                    'Ocasional'      => ['#FFB74D', '#000', 'bi-person-badge-fill'],
                                    'Cátedra'        => ['#FFE0B2', '#000', 'bi-person-badge-fill'],
                                    default          => ['#2E7D32', '#fff', 'bi-person-fill'],
                                };
                            @endphp
                            <tr>
                                <td class="text-center text-muted small">
                                    {{ $inc->fila ?? '—' }}
                                </td>
                                <td class="small">
                                    @if($inc->periodo)
                                        <span class="badge bg-light text-dark border"
                                              style="font-size:.7rem">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $inc->periodo->nombre }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small">
                                    <span class="badge" style="background-color:{{ $rolBadge[0] }};color:{{ $rolBadge[1] }};font-size:.65rem">
                                        <i class="bi {{ $rolBadge[2] }} me-1"></i>{{ $inc->nombre_rol ?? 'Estudiante' }}
                                    </span>
                                </td>
                                <td class="small fw-semibold">{{ $inc->documento ?: '—' }}</td>
                                <td class="small">
                                    {{ trim($inc->nombres . ' ' . $inc->apellidos) ?: '—' }}
                                </td>
                                <td class="small">
                                    @if($inc->codigo_sede)
                                        <span class="badge bg-secondary me-1"
                                              style="font-size:.65rem">{{ $inc->codigo_sede }}</span>
                                        {{ $inc->nombre_sede }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small">
                                    <span class="badge {{ $badgeColor }} me-1"
                                          style="font-size:.65rem">{{ $campoLabel }}</span>
                                    <span class="text-danger"
                                          title="{{ $inc->error }}"
                                          style="cursor:help">
                                        {{ \Illuminate\Support\Str::limit($inc->error, 80) }}
                                    </span>
                                </td>
                                <td class="text-end pe-2">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('usuarios.inconsistencias.edit', $inc) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Corregir">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('usuarios.inconsistencias.destroy', $inc) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar esta inconsistencia?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    title="Eliminar">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $inconsistencias->links() }}
        </div>
    @endif

</x-layout>
