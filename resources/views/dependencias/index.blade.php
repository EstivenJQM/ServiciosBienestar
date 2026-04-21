<x-layout title="Dependencias">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-diagram-3-fill me-2" style="color:#fd7e14"></i>Dependencias
        </h2>
        <a href="{{ route('dependencias.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nueva Dependencia
        </a>
    </div>

    <form method="GET" action="{{ route('dependencias.index') }}" class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <div class="input-group" style="max-width:400px">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="busqueda" value="{{ $busqueda }}"
                       class="form-control"
                       placeholder="Nombre…">
            </div>
            <button type="submit" class="btn btn-sibi">
                <i class="bi bi-search me-1"></i>Buscar
            </button>
            @if($busqueda)
                <a href="{{ route('dependencias.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Limpiar
                </a>
            @endif
        </div>
    </form>

    @forelse($dependencias as $dependencia)
        <div class="tree-area rounded p-3 mb-2 bg-white shadow-sm" style="border-left-color:#fd7e14">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill px-2" style="background-color:#fd7e14">Dependencia</span>
                    <span class="fw-semibold">{{ $dependencia->nombre }}</span>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('dependencias.edit', $dependencia) }}"
                       class="btn btn-sm btn-outline-secondary" title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <form action="{{ route('dependencias.destroy', $dependencia) }}" method="POST"
                          class="d-inline"
                          onsubmit="return confirm('¿Eliminar la dependencia «{{ $dependencia->nombre }}»?')">
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
                No hay dependencias registradas.
            </p>
        </x-card>
    @endforelse

</x-layout>
