<x-layout title="Tipos de Actividad">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-tags-fill me-2 text-success"></i>Tipos de Actividad
        </h2>
        <a href="{{ route('tipo-actividad.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Tipo
        </a>
    </div>

    @forelse($tiposActividad as $tipo)
        @php $tipoId = 'tipo-' . $tipo->id_tipo_actividad; @endphp

        <div class="card shadow-sm mb-2">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">

                    {{-- Nombre colapsable --}}
                    <button class="btn btn-link text-start p-0 text-decoration-none d-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $tipoId }}"
                            aria-expanded="false">
                        <span class="badge px-3 py-2 fs-6 flex-shrink-0" style="background-color:#fd7e14">
                            <i class="bi bi-tag-fill me-1"></i>Tipo
                        </span>
                        <span class="text-dark fw-semibold d-flex align-items-center gap-1">
                            <i class="bi bi-chevron-right toggle-icon" style="font-size:.7rem;transition:transform .2s"></i>
                            {{ $tipo->nombre }}
                            @if($tipo->lineas->isNotEmpty())
                                <span class="badge fw-normal ms-1" style="font-size:.65rem;background-color:#ffe5cc;color:#fd7e14">
                                    {{ $tipo->lineas->count() }}
                                </span>
                            @endif
                        </span>
                    </button>

                    {{-- Acciones --}}
                    <div class="d-flex gap-1 ms-3 flex-shrink-0">
                        <a href="{{ route('tipo-actividad.edit', $tipo) }}"
                           class="btn btn-sm btn-warning" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <form action="{{ route('tipo-actividad.destroy', $tipo) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar el tipo «{{ $tipo->nombre }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Líneas asociadas agrupadas por Área › Componente --}}
                <div class="collapse mt-2 ms-3" id="{{ $tipoId }}">
                    @if($tipo->lineas->isNotEmpty())
                        @php $porComponente = $tipo->lineas->groupBy('id_componente'); @endphp

                        @foreach($porComponente as $idComp => $lineas)
                            @php $comp = $lineas->first()->componente @endphp

                            <div class="mb-2">
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <span class="badge" style="background-color:#3369b3;font-size:.7rem">{{ $comp->area->nombre }}</span>
                                    <i class="bi bi-chevron-right text-muted" style="font-size:.65rem"></i>
                                    <span class="badge" style="background-color:#6f42c1;font-size:.7rem">{{ $comp->nombre }}</span>
                                </div>
                                <div class="ms-3 d-flex flex-wrap gap-1">
                                    @foreach($lineas as $linea)
                                        <span class="badge rounded-pill px-2 py-1" style="background-color:#20c997;font-size:.75rem">
                                            <i class="bi bi-arrow-right-short"></i>{{ $linea->nombre }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">Sin líneas asociadas.</p>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay tipos de actividad registrados.
            </p>
        </x-card>
    @endforelse

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
