@props(['area'])

@php $areaId = 'area-' . $area->id_area; @endphp

<div class="tree-area rounded p-3 mb-3 bg-white shadow-sm" style="border-left-color:#3369b3">

    {{-- Área --}}
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 flex-grow-1">
            <span class="badge rounded-pill px-2" style="background-color:#3369b3">Área</span>
            <button class="btn btn-link text-start fw-bold fs-6 p-0 text-decoration-none text-dark d-flex align-items-center gap-1"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $areaId }}"
                    aria-expanded="false">
                <i class="bi bi-chevron-right toggle-icon" style="font-size:.75rem;transition:transform .2s"></i>
                {{ $area->nombre }}
                @if($area->componentes->isNotEmpty())
                    <span class="badge fw-normal ms-1" style="font-size:.7rem;background-color:#d0dff5;color:#3369b3">
                        {{ $area->componentes->count() }}
                    </span>
                @endif
            </button>
        </div>
        <div class="d-flex gap-1">
            <a href="{{ route('areas.edit', $area) }}"
               class="btn btn-sm btn-warning" title="Editar área">
                <i class="bi bi-pencil-fill"></i>
            </a>
            <form action="{{ route('areas.destroy', $area) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('¿Eliminar el área «{{ $area->nombre }}»?\nSe eliminarán también sus componentes y líneas.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar área">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Componentes (colapsables) --}}
    <div class="collapse mt-2 ms-3" id="{{ $areaId }}">
        @forelse($area->componentes as $componente)
            @php $compId = 'comp-' . $componente->id_componente; @endphp

            <div class="tree-comp rounded p-2 mb-2 bg-light">
                <button class="btn btn-link text-start p-0 text-decoration-none fw-semibold d-flex align-items-center gap-2 w-100"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $compId }}"
                        aria-expanded="false">
                    <span class="badge rounded-pill px-2 flex-shrink-0"
                          style="background-color:#6f42c1">Componente</span>
                    <span class="text-dark d-flex align-items-center gap-1">
                        <i class="bi bi-chevron-right toggle-icon" style="font-size:.7rem;transition:transform .2s"></i>
                        {{ $componente->nombre }}
                        @if($componente->lineas->isNotEmpty())
                            <span class="badge bg-opacity-25 fw-normal ms-1" style="font-size:.65rem;background-color:#e0d0f5;color:#6f42c1">
                                {{ $componente->lineas->count() }}
                            </span>
                        @endif
                    </span>
                </button>

                {{-- Líneas --}}
                <div class="collapse mt-2 ms-3" id="{{ $compId }}">
                    @forelse($componente->lineas as $linea)
                        @php $lineaId = 'linea-' . $linea->id_linea; @endphp

                        <div class="tree-linea rounded px-2 py-1 mb-1 bg-white">
                            <button class="btn btn-link text-start p-0 text-decoration-none small d-flex align-items-center gap-2 w-100"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $lineaId }}"
                                    aria-expanded="false">
                                <span class="badge rounded-pill px-2 flex-shrink-0"
                                      style="background-color:#20c997">Línea</span>
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
        @empty
            <p class="text-muted small mb-0">Sin componentes registrados.</p>
        @endforelse
    </div>

</div>

