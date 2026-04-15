<x-layout title="Componentes">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><i class="bi bi-collection-fill me-2" style="color:#6f42c1"></i>Componentes</h2>
        <a href="{{ route('componentes.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Componente
        </a>
    </div>

    @if($componentes->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay componentes registrados.
            </p>
        </x-card>
    @else
        @foreach($areas as $idArea => $area)
            @if($componentes->has($idArea))
                <div class="d-flex align-items-center gap-2 mb-2 mt-3">
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-diagram-3 me-1"></i>{{ $area->nombre }}
                    </span>
                </div>

                @foreach($componentes[$idArea] as $componente)
                    @php $compId = 'comp-idx-' . $componente->id_componente; @endphp

                    <div class="tree-comp rounded p-3 mb-2 bg-white shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">

                            {{-- Nombre colapsable --}}
                            <button class="btn btn-link text-start p-0 text-decoration-none fw-semibold d-flex align-items-center gap-2"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $compId }}"
                                    aria-expanded="false">
                                <span class="badge rounded-pill px-2 flex-shrink-0" style="background-color:#6f42c1">Componente</span>
                                <span class="text-dark d-flex align-items-center gap-1">
                                    <i class="bi bi-chevron-right toggle-icon" style="font-size:.7rem;transition:transform .2s"></i>
                                    {{ $componente->nombre }}
                                    @if($componente->lineas->isNotEmpty())
                                        <span class="badge fw-normal ms-1" style="font-size:.65rem;background-color:#e0d0f5;color:#6f42c1">
                                            {{ $componente->lineas->count() }}
                                        </span>
                                    @endif
                                </span>
                            </button>

                            <div class="d-flex gap-1 ms-3 flex-shrink-0">
                                <a href="{{ route('componentes.edit', $componente) }}"
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form action="{{ route('componentes.destroy', $componente) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar el componente «{{ $componente->nombre }}»?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Líneas --}}
                        <div class="collapse mt-2 ms-3" id="{{ $compId }}">
                            @forelse($componente->lineas as $linea)
                                @php $lineaId = 'linea-idx-' . $linea->id_linea; @endphp

                                <div class="tree-linea rounded px-2 py-1 mb-1 bg-light">
                                    <button class="btn btn-link text-start p-0 text-decoration-none small d-flex align-items-center gap-2 w-100"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $lineaId }}"
                                            aria-expanded="false">
                                        <span class="badge rounded-pill px-2 flex-shrink-0" style="background-color:#20c997">Línea</span>
                                        <span class="text-dark d-flex align-items-center gap-1">
                                            <i class="bi bi-chevron-right toggle-icon" style="font-size:.65rem;transition:transform .2s"></i>
                                            {{ $linea->nombre }}
                                            @if($linea->tiposActividad->isNotEmpty())
                                                <span class="badge fw-normal ms-1" style="font-size:.6rem;background-color:#d0f5ed;color:#20c997">
                                                    {{ $linea->tiposActividad->count() }}
                                                </span>
                                            @endif
                                        </span>
                                    </button>

                                    {{-- Tipos de Actividad --}}
                                    <div class="collapse mt-1 ms-3" id="{{ $lineaId }}">
                                        @forelse($linea->tiposActividad as $tipo)
                                            <div class="d-flex align-items-center gap-2 py-1 px-2 rounded mb-1"
                                                 style="background-color:#fff8e1">
                                                <i class="bi bi-tag-fill" style="color:#fd7e14;font-size:.75rem"></i>
                                                <span class="small">{{ $tipo->nombre }}</span>
                                            </div>
                                        @empty
                                            <p class="text-muted small mb-0">Sin tipos de actividad asociados.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">Sin líneas registradas.</p>
                            @endforelse
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
