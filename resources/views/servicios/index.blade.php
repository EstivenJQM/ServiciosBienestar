<x-layout title="Servicios">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-tools me-2" style="color:#198754"></i>Servicios
        </h2>
        <a href="{{ route('servicios.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Servicio
        </a>
    </div>

    @if($periodos->isEmpty() || $servicios->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay servicios registrados.
            </p>
        </x-card>
    @else
        @foreach($periodos as $idPeriodo => $periodo)
            @if($servicios->has($idPeriodo))
                <div class="d-flex align-items-center gap-2 mt-4 mb-2">
                    <span class="badge bg-dark px-3 py-2 fs-6">
                        <i class="bi bi-calendar3 me-1"></i>{{ $periodo->nombre }}
                    </span>
                </div>

                <x-card :padding="false">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Línea</th>
                                <th>Tipo de Actividad</th>
                                <th>Sede</th>
                                <th>Fecha</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicios[$idPeriodo] as $servicio)
                                <tr>
                                    <td class="align-middle fw-semibold">{{ $servicio->nombre }}</td>
                                    <td class="align-middle">
                                        <span class="badge" style="background-color:#20c997">
                                            {{ $servicio->linea->nombre }}
                                        </span>
                                        <small class="text-muted d-block">
                                            {{ $servicio->linea->componente->area->nombre }}
                                            › {{ $servicio->linea->componente->nombre }}
                                        </small>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge" style="background-color:#6f42c1">
                                            {{ $servicio->tipoActividad->nombre }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        {{ $servicio->sede->nombre }}
                                    </td>
                                    <td class="align-middle">
                                        {{ $servicio->fecha->format('d/m/Y') }}
                                    </td>
                                    <td class="text-end align-middle">
                                        <a href="{{ route('servicios.edit', $servicio) }}"
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('servicios.destroy', $servicio) }}" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar el servicio «{{ $servicio->nombre }}»?')">
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
        @endforeach
    @endif

</x-layout>
