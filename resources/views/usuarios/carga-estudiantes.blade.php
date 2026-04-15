<x-layout title="Carga de Estudiantes">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-upload me-2 text-primary"></i>Carga Masiva — Estudiantes
        </h2>
    </div>

    {{-- ── Formulario de carga ── --}}
    <div class="row">
        <div class="col-md-6">
            <x-card title="<i class='bi bi-file-earmark-spreadsheet me-2'></i>Cargar archivo CSV" color="primary">
                <form action="{{ route('usuarios.carga-estudiantes.store') }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="id_periodo" class="form-label fw-semibold">
                            Período <span class="text-danger">*</span>
                        </label>
                        <select id="id_periodo" name="id_periodo" required
                                class="form-select {{ $errors->has('id_periodo') ? 'is-invalid' : '' }}">
                            <option value="">-- Seleccione un período --</option>
                            @foreach($periodos as $periodo)
                                <option value="{{ $periodo->id_periodo }}"
                                    {{ old('id_periodo') == $periodo->id_periodo ? 'selected' : '' }}>
                                    {{ $periodo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_periodo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($periodos->isEmpty())
                            <div class="form-text text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                No hay períodos registrados.
                                <a href="{{ route('periodos.create') }}">Crear uno</a>.
                            </div>
                        @endif
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
                        <div class="form-text">
                            Formato UTF-8, separado por punto y coma (;). Máx. 20 MB.
                        </div>
                    </div>

                    {{-- Formato esperado --}}
                    <div class="alert alert-light border mb-3 p-2">
                        <p class="small fw-semibold mb-1">
                            <i class="bi bi-info-circle me-1 text-primary"></i>Columnas esperadas:
                        </p>
                        <code class="small d-block text-wrap" style="font-size:.72rem">
                            DOCUMENTO ; NOMBRES ; APELLIDOS ; EMAIL ; SEDE ; NOMBRE DE LA SEDE ; PLAN ; PROGRAMA ACADEMICO ; FACULTAD
                        </code>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i> Procesar archivo
                    </button>
                </form>
            </x-card>
        </div>

        {{-- ── Resultado ── --}}
        @if(session('resultado'))
            @php $r = session('resultado'); @endphp
            <div class="col-md-6">
                <x-card title="<i class='bi bi-clipboard-check me-2'></i>Resultado de la carga" color="success">

                    <div class="row text-center g-3 mb-3">
                        <div class="col-4">
                            <div class="p-3 rounded" style="background:#e6f2ec">
                                <div class="fs-2 fw-bold" style="color:#196844">{{ $r['creados'] }}</div>
                                <div class="small text-muted">Nuevos</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded bg-light">
                                <div class="fs-2 fw-bold text-primary">{{ $r['actualizados'] }}</div>
                                <div class="small text-muted">Actualizados</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded" style="background:#fef2f2">
                                <div class="fs-2 fw-bold text-danger">{{ count($r['errores']) }}</div>
                                <div class="small text-muted">Errores</div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mb-2">
                        <i class="bi bi-grid-3x3 me-1"></i>
                        Total filas procesadas: <strong>{{ $r['total'] }}</strong>
                    </p>

                    @if(! empty($r['errores']))
                        <p class="fw-semibold small mb-1 text-danger">
                            <i class="bi bi-exclamation-circle me-1"></i>Detalle de errores:
                        </p>
                        <div style="max-height:260px;overflow-y:auto">
                            <ul class="list-unstyled mb-0">
                                @foreach($r['errores'] as $error)
                                    <li class="small py-1 border-bottom text-danger">
                                        <i class="bi bi-x-circle me-1"></i>{{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="alert alert-success py-2 mb-0 small">
                            <i class="bi bi-check-circle me-1"></i>Sin errores. Carga completada exitosamente.
                        </div>
                    @endif

                </x-card>
            </div>
        @endif
    </div>

</x-layout>
