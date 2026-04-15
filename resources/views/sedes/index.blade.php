<x-layout title="Sedes">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-geo-alt-fill me-2" style="color:#196844"></i>Sedes
        </h2>
        <a href="{{ route('sedes.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nueva Sede
        </a>
    </div>

    @forelse($sedes as $sede)
        <div class="tree-area rounded p-3 mb-2 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill px-2" style="background-color:#196844">Sede</span>
                    <span class="badge bg-secondary">{{ $sede->codigo }}</span>
                    <span class="fw-semibold">{{ $sede->nombre }}</span>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('sedes.edit', $sede) }}"
                       class="btn btn-sm btn-warning" title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <form action="{{ route('sedes.destroy', $sede) }}" method="POST"
                          class="d-inline"
                          onsubmit="return confirm('¿Eliminar la sede «{{ $sede->nombre }}»?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay sedes registradas.
            </p>
        </x-card>
    @endforelse

</x-layout>
