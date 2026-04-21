<x-layout title="Servicios">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-tools me-2" style="color:#196844"></i>Servicios
        </h2>
        <a href="{{ route('servicios.create') }}" class="btn btn-sibi">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Servicio
        </a>
    </div>

    @if($periodos->isEmpty() || $servicios->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-4">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                No hay servicios registrados.
            </p>
        </x-card>
    @else
        @php
            $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        @endphp

        @foreach($periodos as $idPeriodo => $periodo)
            @if($servicios->has($idPeriodo))
                @php
                    $lista     = $servicios[$idPeriodo];
                    $partes    = explode('-', $periodo->nombre);
                    $esPrimero = ($partes[1] ?? '1') === '1';
                @endphp

                {{-- Encabezado de período --}}
                <div class="d-flex align-items-center justify-content-between mt-4 mb-2 px-1">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold fs-5" style="color:#196844">
                            <i class="bi bi-calendar3 me-1"></i>{{ $periodo->nombre }}
                        </span>
                        <span class="badge"
                              style="background-color:{{ $esPrimero ? '#196844' : '#ffd400' }};color:{{ $esPrimero ? '#fff' : '#000' }};font-size:.72rem">
                            {{ $esPrimero ? 'Primer semestre' : 'Segundo semestre' }}
                        </span>
                    </div>
                    <span class="badge bg-light text-dark border" style="font-size:.75rem">
                        {{ $lista->count() }} {{ $lista->count() === 1 ? 'servicio' : 'servicios' }}
                    </span>
                </div>

                <x-card :padding="false">
                    <table class="table table-hover table-sm mb-0 align-middle" style="table-layout:fixed;width:100%">
                        <colgroup>
                            <col style="width:55px">
                            <col style="width:18%">
                            <col style="width:38%">
                            <col style="width:24%">
                            <col style="width:80px">
                            <col style="width:110px">
                        </colgroup>
                        <thead class="table-light">
                            <tr>
                                <th></th>
                                <th>Nombre</th>
                                <th>Línea / Tipo</th>
                                <th>Sede</th>
                                <th class="text-center">Usuarios</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lista as $servicio)
                                @php
                                    $mes = $meses[$servicio->fecha->month - 1];
                                @endphp
                                <tr>
                                    {{-- Mini calendario --}}
                                    <td class="text-center px-2">
                                        <div style="width:38px;border:1px solid #dee2e6;border-radius:.375rem;overflow:hidden;font-size:.75rem;line-height:1">
                                            <div style="background:#196844;color:#fff;padding:2px 0;font-size:.6rem;letter-spacing:.03em">
                                                {{ $mes }}
                                            </div>
                                            <div class="fw-bold py-1" style="font-size:1rem">
                                                {{ $servicio->fecha->format('d') }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Nombre --}}
                                    <td>
                                        <a href="{{ route('servicios.show', $servicio) }}"
                                           class="fw-semibold text-decoration-none"
                                           style="color:#196844">
                                            {{ $servicio->nombre }}
                                        </a>
                                    </td>

                                    {{-- Línea + Tipo de actividad --}}
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 align-items-center">
                                            <span class="badge" style="background-color:#20c997;font-size:.68rem">
                                                {{ $servicio->linea->nombre }}
                                            </span>
                                            <span class="badge" style="background-color:#6f42c1;font-size:.68rem">
                                                {{ $servicio->tipoActividad->nombre }}
                                            </span>
                                        </div>
                                        <small class="text-muted" style="font-size:.7rem">
                                            {{ $servicio->linea->componente->area->nombre }}
                                            <i class="bi bi-chevron-right" style="font-size:.55rem"></i>
                                            {{ $servicio->linea->componente->nombre }}
                                        </small>
                                    </td>

                                    {{-- Sede --}}
                                    <td>
                                        <span class="badge bg-secondary" style="font-size:.68rem">
                                            {{ $servicio->sede->codigo }}
                                        </span>
                                        <span class="small text-muted">{{ $servicio->sede->nombre }}</span>
                                    </td>

                                    {{-- Usuarios asignados --}}
                                    <td class="text-center">
                                        @if($servicio->usuarios_asignados_count > 0)
                                            <span class="badge rounded-pill"
                                                  style="background-color:#e6f2ec;color:#196844;font-size:.75rem;border:1px solid #196844">
                                                <i class="bi bi-people-fill me-1"></i>{{ $servicio->usuarios_asignados_count }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="text-end">
                                        <div class="d-flex flex-column gap-1 align-items-end">
                                            <a href="{{ route('servicios.show', $servicio) }}"
                                               class="btn btn-sm btn-sibi">
                                                <i class="bi bi-people-fill me-1"></i>Asignar
                                            </a>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('servicios.edit', $servicio) }}"
                                                   class="btn btn-sm btn-outline-secondary" title="Editar">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <form action="{{ route('servicios.destroy', $servicio) }}" method="POST"
                                                      onsubmit="return confirm('¿Eliminar el servicio «{{ $servicio->nombre }}»?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
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
