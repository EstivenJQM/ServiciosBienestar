<x-layout title="Líneas">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-list-ul me-2" style="color:#20c997"></i>Líneas
        </h2>
        <a href="{{ route('lineas.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nueva Línea
        </a>
    </div>

    {{-- Búsqueda --}}
    <form method="GET" action="{{ route('lineas.index') }}" class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <div class="input-group" style="max-width:400px">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="busqueda" value="{{ $busqueda }}"
                       class="form-control"
                       placeholder="Nombre de línea o componente…">
            </div>

            <button type="submit" class="btn btn-sibi">
                <i class="bi bi-search me-1"></i>Buscar
            </button>

            @if($busqueda)
                <a href="{{ route('lineas.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Limpiar
                </a>
            @endif
        </div>
    </form>

    @if($componentes->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay líneas registradas.
            </p>
        </x-card>
    @else
        @foreach($componentes as $idComp => $componente)
            @if($lineas->has($idComp))
                {{-- Cabeceras Área › Componente --}}
                <div class="d-flex align-items-center gap-2 mt-3 mb-1">
                    <span class="badge px-3 py-2 fs-6" style="background-color:#3369b3">
                        <i class="bi bi-diagram-3 me-1"></i>{{ $componente->area->nombre }}
                    </span>
                    <i class="bi bi-chevron-right text-muted"></i>
                    <span class="badge px-3 py-2 fs-6" style="background-color:#6f42c1">
                        <i class="bi bi-collection me-1"></i>{{ $componente->nombre }}
                    </span>
                </div>

                @foreach($lineas[$idComp] as $linea)
                    @php $lineaId = 'linea-idx-' . $linea->id_linea; @endphp

                    <div class="tree-linea rounded p-3 mb-2 bg-white shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">

                            {{-- Nombre colapsable --}}
                            <button class="btn btn-link text-start p-0 text-decoration-none fw-semibold d-flex align-items-center gap-2"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $lineaId }}"
                                    aria-expanded="false">
                                <span class="badge rounded-pill px-2 flex-shrink-0" style="background-color:#20c997">Línea</span>
                                <span class="text-dark d-flex align-items-center gap-1">
                                    <i class="bi bi-chevron-right toggle-icon" style="font-size:.7rem;transition:transform .2s"></i>
                                    {{ $linea->nombre }}
                                    @if($linea->tiposActividad->isNotEmpty())
                                        <span class="badge fw-normal ms-1" style="font-size:.65rem;background-color:#d0f5ed;color:#20c997">
                                            {{ $linea->tiposActividad->count() }}
                                        </span>
                                    @endif
                                </span>
                            </button>

                            <div class="d-flex gap-1">
                                <a href="{{ route('lineas.edit', $linea) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form action="{{ route('lineas.destroy', $linea) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar la línea «{{ $linea->nombre }}»?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Tipos de Actividad --}}
                        <div class="collapse mt-2 ms-3" id="{{ $lineaId }}">
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
