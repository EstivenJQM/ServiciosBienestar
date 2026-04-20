<x-layout title="Áreas">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><i class="bi bi-diagram-3-fill me-2" style="color:#3369b3"></i>Áreas</h2>
        <a href="{{ route('areas.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nueva Área
        </a>
    </div>

    {{-- Búsqueda --}}
    <form method="GET" action="{{ route('areas.index') }}" class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <div class="input-group" style="max-width:400px">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="busqueda" value="{{ $busqueda }}"
                       class="form-control"
                       placeholder="Nombre del área…">
            </div>

            <button type="submit" class="btn btn-sibi">
                <i class="bi bi-search me-1"></i>Buscar
            </button>

            @if($busqueda)
                <a href="{{ route('areas.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Limpiar
                </a>
            @endif
        </div>
    </form>

    @forelse($areas as $area)
        <x-areas.tree-item :area="$area" />
    @empty
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay áreas registradas.
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
