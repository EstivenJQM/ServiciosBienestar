<x-layout title="Facultades">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-building me-2" style="color:#196844"></i>Facultades
        </h2>
        <a href="{{ route('facultades.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nueva Facultad
        </a>
    </div>

    @forelse($facultades as $facultad)
        @php $collapseId = 'fac-' . $facultad->id_facultad; @endphp

        <div class="tree-area rounded p-3 mb-2 bg-white shadow-sm">

            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <span class="badge rounded-pill px-2" style="background-color:#196844">Facultad</span>
                    <button class="btn btn-link text-start fw-bold fs-6 p-0 text-decoration-none text-dark d-flex align-items-center gap-1"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $collapseId }}"
                            aria-expanded="false">
                        <i class="bi bi-chevron-right toggle-icon" style="font-size:.75rem;transition:transform .2s"></i>
                        {{ $facultad->nombre }}
                        @if($facultad->sedes->isNotEmpty())
                            <span class="badge fw-normal ms-1" style="font-size:.7rem;background-color:#d1e7dd;color:#196844">
                                {{ $facultad->sedes->count() }}
                            </span>
                        @endif
                    </button>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('facultades.edit', $facultad) }}"
                       class="btn btn-sm btn-warning" title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <form action="{{ route('facultades.destroy', $facultad) }}" method="POST"
                          class="d-inline"
                          onsubmit="return confirm('¿Eliminar la facultad «{{ $facultad->nombre }}»?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="collapse mt-2 ms-3" id="{{ $collapseId }}">
                @if($facultad->sedes->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($facultad->sedes as $sede)
                            <span class="badge d-flex align-items-center gap-1 px-2 py-1"
                                  style="background-color:#196844; font-size:.78rem">
                                <span class="badge bg-white text-dark" style="font-size:.68rem">
                                    {{ $sede->codigo }}
                                </span>
                                {{ $sede->nombre }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-0">Sin sedes asociadas.</p>
                @endif
            </div>

        </div>
    @empty
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay facultades registradas.
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
