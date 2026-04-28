<x-layout title="Servicio — {{ $servicio->nombre }}">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-clipboard2-heart-fill me-2" style="color:#196844"></i>{{ $servicio->nombre }}
            </h2>
            <div class="d-flex flex-wrap gap-2 small">
                <span class="badge bg-dark">
                    <i class="bi bi-calendar3 me-1"></i>{{ $servicio->periodo->nombre }}
                </span>
                <span class="badge bg-secondary">
                    <i class="bi bi-geo-alt-fill me-1"></i>{{ $servicio->sede->nombre }}
                </span>
                <span class="badge" style="background-color:#20c997">
                    {{ $servicio->linea->nombre }}
                </span>
                <span class="badge" style="background-color:#3DFF5E;color:#000">
                    {{ $servicio->tipoActividad->nombre }}
                </span>
                <span class="badge bg-light text-dark border">
                    <i class="bi bi-calendar-event me-1"></i>{{ $servicio->fecha->format('d/m/Y') }}
                </span>
            </div>
        </div>
        <a href="{{ route('servicios.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    {{-- Carga + Resultado --}}
    <div class="row mb-4">

        {{-- Formulario de carga --}}
        <div class="col-md-5">
            <x-card title="Asignar usuarios" color="sibi">
                <form action="{{ route('servicios.usuarios.store', $servicio) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf

                    <p class="small text-muted mb-3">
                        Se vinculan usuarios que tengan el rol indicado activo
                        durante el período <strong>{{ $servicio->periodo->nombre }}</strong>.
                    </p>

                    <div class="alert alert-light border p-2 mb-3">
                        <p class="small fw-semibold mb-1">
                            <i class="bi bi-info-circle me-1" style="color:#196844"></i>Columnas esperadas:
                        </p>
                        <code class="small">DOCUMENTO ; ROL</code>
                        <p class="small text-muted mt-1 mb-0">
                            Ejemplo: <code>1234567 ; Estudiante</code><br>
                            Roles válidos: <code>Estudiante</code>, <code>Graduado</code>, <code>Familiar</code>, <code>Administrativo</code>, <code>Docente</code>, <code>Contratista</code>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="archivo" class="form-label fw-semibold">
                            Archivo CSV <span class="text-danger">*</span>
                        </label>
                        <input type="file" id="archivo" name="archivo" accept=".csv,.txt"
                               class="form-control {{ $errors->has('archivo') ? 'is-invalid' : '' }}">
                        @error('archivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Formato UTF-8, separado por punto y coma (;). Máx. 5 MB.</div>
                    </div>

                    <button type="submit" class="btn btn-sibi w-100">
                        <i class="bi bi-upload me-1"></i> Procesar y asignar
                    </button>
                </form>
            </x-card>
        </div>

        {{-- Resultado --}}
        @if(session('resultado_asignacion'))
            @php $r = session('resultado_asignacion'); @endphp
            <div class="col-md-7">
                <x-card title="Resultado de la asignación" color="sibi">

                    <div class="row text-center g-2 mb-3">
                        <div class="col-3">
                            <div class="p-2 rounded" style="background:#e6f2ec">
                                <div class="fs-3 fw-bold" style="color:#196844">{{ $r['asignados'] }}</div>
                                <div class="small text-muted">Asignados</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded bg-light">
                                <div class="fs-3 fw-bold text-secondary">{{ $r['ya_existian'] }}</div>
                                <div class="small text-muted">Ya existían</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded" style="background:#fff8e1">
                                <div class="fs-3 fw-bold text-warning">{{ count($r['sin_rol']) }}</div>
                                <div class="small text-muted">Sin rol</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded" style="background:#fef2f2">
                                <div class="fs-3 fw-bold text-danger">{{ count($r['no_encontrados']) }}</div>
                                <div class="small text-muted">No hallados</div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mb-2">
                        <i class="bi bi-grid-3x3 me-1"></i>Total cédulas procesadas: <strong>{{ $r['total'] }}</strong>
                    </p>

                    @if(! empty($r['no_encontrados']))
                        <p class="small fw-semibold mb-1 text-danger">
                            <i class="bi bi-person-x me-1"></i>No encontrados en el sistema:
                        </p>
                        <div style="max-height:100px;overflow-y:auto" class="mb-2">
                            <ul class="list-unstyled mb-0">
                                @foreach($r['no_encontrados'] as $doc)
                                    <li class="small text-danger"><i class="bi bi-x-circle me-1"></i>{{ $doc }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(! empty($r['sin_rol']))
                        <p class="small fw-semibold mb-1 text-warning">
                            <i class="bi bi-person-exclamation me-1"></i>Sin rol activo en esta sede/período:
                        </p>
                        <div style="max-height:100px;overflow-y:auto">
                            <ul class="list-unstyled mb-0">
                                @foreach($r['sin_rol'] as $entrada)
                                    <li class="small text-warning"><i class="bi bi-exclamation-circle me-1"></i>{{ $entrada }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(empty($r['no_encontrados']) && empty($r['sin_rol']))
                        <div class="alert alert-success py-2 mb-0 small">
                            <i class="bi bi-check-circle me-1"></i>Todos los usuarios fueron asignados correctamente.
                        </div>
                    @endif

                </x-card>
            </div>
        @endif
    </div>

    {{-- Lista de usuarios asignados --}}
    <x-card title="Usuarios asignados ({{ $servicio->usuariosAsignados->count() }})" color="sibi">
        @if($servicio->usuariosAsignados->isEmpty())
            <p class="text-center text-muted py-3 mb-0">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                Aún no hay usuarios asignados a este servicio.
            </p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servicio->usuariosAsignados->sortBy(fn($u) => $u->usuario?->primer_apellido) as $urs)
                            @php
                                $tipoEmp  = $urs->empleado?->tipoEmpleado?->nombre;
                                $cargoNom = $urs->empleado?->cargo?->nombre;
                                $esEmpleado = $urs->rol?->nombre === 'Empleado';

                                $rolColor = match(true) {
                                    $urs->rol?->nombre === 'Estudiante'              => '#196844',
                                    $urs->rol?->nombre === 'Graduado'                => '#0d6efd',
                                    $esEmpleado && $tipoEmp === 'Contratista'        => '#6f42c1',
                                    $esEmpleado && $tipoEmp === 'Administrativo'     => '#0d6efd',
                                    $esEmpleado && $tipoEmp === 'Docente'            => '#856404',
                                    $esEmpleado                                      => '#856404',
                                    default                                          => '#6c757d',
                                };
                                $etiqueta = match(true) {
                                    $esEmpleado && $tipoEmp === 'Docente' => ($cargoNom ?? 'Docente'),
                                    $esEmpleado                           => ($tipoEmp ?? 'Empleado'),
                                    default                               => ($urs->rol?->nombre ?? '—'),
                                };
                            @endphp
                            <tr>
                                <td class="small fw-semibold">
                                    {{ $urs->usuario?->documento ?? '—' }}
                                </td>
                                <td class="small">
                                    {{ $urs->usuario?->nombre_completo ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge" style="background-color:{{ $rolColor }};font-size:.68rem">
                                        {{ $etiqueta }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger"
                                            title="Desvincular"
                                            onclick="desvincular('{{ route('servicios.usuarios.destroy', [$servicio, $urs->id_usuario_rol_sede]) }}')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <form id="form-desvincular" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function desvincular(url) {
            if (!confirm('¿Desvincular este usuario del servicio?')) return;
            const form = document.getElementById('form-desvincular');
            form.action = url;
            form.submit();
        }
    </script>

</x-layout>
