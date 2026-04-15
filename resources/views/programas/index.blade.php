<x-layout title="Programas">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-journal-bookmark-fill me-2" style="color:#196844"></i>Programas
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('programas.asignacion-snies') }}" class="btn btn-outline-sibi">
                <i class="bi bi-card-checklist me-1"></i> Asignar SNIES / Tipo formación
            </a>
            <a href="{{ route('programas.create') }}" class="btn btn-sibi">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Programa
            </a>
        </div>
    </div>

    @if($programas->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay programas registrados.
            </p>
        </x-card>
    @else
        @foreach($facultades as $idFacultad => $facultad)
            @if($programas->has($idFacultad))

                {{-- Cabecera facultad --}}
                <div class="d-flex align-items-center gap-2 mt-3 mb-2">
                    <span class="badge px-3 py-2 fs-6" style="background-color:#196844">
                        <i class="bi bi-building me-1"></i>{{ $facultad->nombre }}
                    </span>
                </div>

                @foreach($programas[$idFacultad] as $programa)
                    @php
                        $collapseId  = 'prog-' . $programa->id_programa;
                        $psBySede    = $programa->programaSedes->keyBy('id_sede');
                        $totalPlanes = $programa->programaSedes->sum(fn($ps) => $ps->planesEstudio->count());
                        $conteo      = $totalPlanes ?: $programa->sedes->count();
                    @endphp

                    <div class="tree-area rounded p-3 mb-2 bg-white shadow-sm">

                        {{-- Fila principal --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <span class="badge rounded-pill px-2" style="background-color:#196844">Programa</span>
                                <button class="btn btn-link text-start fw-bold fs-6 p-0 text-decoration-none text-dark d-flex align-items-center gap-1"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#{{ $collapseId }}"
                                        aria-expanded="false">
                                    <i class="bi bi-chevron-right toggle-icon" style="font-size:.75rem;transition:transform .2s"></i>
                                    {{ $programa->nombre }}
                                    @if($conteo)
                                        <span class="badge fw-normal ms-1" style="font-size:.7rem;background-color:#d1e7dd;color:#196844">
                                            {{ $conteo }}
                                        </span>
                                    @endif
                                </button>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('programas.edit', $programa) }}"
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form action="{{ route('programas.destroy', $programa) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar el programa «{{ $programa->nombre }}»?')">
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

                            {{-- Tipo de formación --}}
                            @if($programa->tipoFormacion)
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-secondary" style="font-size:.72rem">
                                        {{ $programa->tipoFormacion->nivel->nombre }}
                                    </span>
                                    <span class="badge bg-light text-dark border" style="font-size:.72rem">
                                        {{ $programa->tipoFormacion->nombre }}
                                    </span>
                                </div>
                            @endif

                            {{-- Tabla de planes por sede --}}
                            @if($programa->sedes->isNotEmpty())
                                <div class="border rounded-3" style="overflow:hidden;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0" style="font-size:.82rem;margin-bottom:0!important">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:110px">Código plan</th>
                                                <th>Sede</th>
                                                <th style="width:130px">SNIES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($programa->sedes as $sede)
                                                @php
                                                    $ps    = $psBySede[$sede->id_sede] ?? null;
                                                    $plans = $ps ? $ps->planesEstudio : collect();
                                                @endphp

                                                @if($plans->isEmpty())
                                                    <tr>
                                                        <td class="text-muted text-center">—</td>
                                                        <td>
                                                            <span class="badge bg-secondary me-1">{{ $sede->codigo }}</span>
                                                            {{ $sede->nombre }}
                                                        </td>
                                                        <td>
                                                            @if($sede->pivot->codigo_snies)
                                                                <span class="badge bg-warning text-dark">{{ $sede->pivot->codigo_snies }}</span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @else
                                                    @foreach($plans as $plan)
                                                        <tr>
                                                            <td>
                                                                <span class="badge bg-info text-dark">{{ $plan->codigo_plan }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary me-1">{{ $sede->codigo }}</span>
                                                                {{ $sede->nombre }}
                                                            </td>
                                                            <td>
                                                                @if($sede->pivot->codigo_snies)
                                                                    <span class="badge bg-warning text-dark">{{ $sede->pivot->codigo_snies }}</span>
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                </div>
                            @else
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-dash-circle me-1"></i>Sin sedes asociadas.
                                </p>
                            @endif

                        </div>
                    </div>
                @endforeach

            @endif
        @endforeach
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
