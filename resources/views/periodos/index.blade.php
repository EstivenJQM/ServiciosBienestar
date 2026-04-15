<x-layout title="Períodos">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-calendar3 me-2" style="color:#0d6efd"></i>Períodos
        </h2>
        <a href="{{ route('periodos.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Período
        </a>
    </div>

    @if($periodos->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay períodos registrados.
            </p>
        </x-card>
    @else
        <x-card :padding="false">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Período</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($periodos as $periodo)
                        <tr>
                            <td class="align-middle fw-semibold">
                                <i class="bi bi-calendar-event me-2 text-primary"></i>
                                {{ $periodo->nombre }}
                            </td>
                            <td class="text-end align-middle">
                                <a href="{{ route('periodos.edit', $periodo) }}"
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form action="{{ route('periodos.destroy', $periodo) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Eliminar el período «{{ $periodo->nombre }}»?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>
    @endif

</x-layout>
