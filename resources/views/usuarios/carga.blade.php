<x-layout title="Carga Masiva de Usuarios">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-upload me-2 text-primary"></i>Carga Masiva de Usuarios
        </h2>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-card title="Configurar carga" color="primary">
                <form action="{{ route('usuarios.carga.store') }}" method="POST"
                      enctype="multipart/form-data" id="form-carga">
                    @csrf

                    {{-- Selector de tipo de usuario --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Tipo de usuario <span class="text-danger">*</span>
                        </label>

                        <div class="d-flex gap-2 flex-wrap">

                            <div class="rol-card flex-fill p-3 rounded border text-center"
                                 data-rol="Estudiante" data-implementado="1" style="cursor:pointer;min-width:110px">
                                <i class="bi bi-person-fill fs-3 d-block mb-1"></i>
                                <span class="small fw-semibold">Estudiante</span>
                            </div>

                            <div class="rol-card flex-fill p-3 rounded border text-center"
                                 data-rol="Graduado" data-implementado="1" style="cursor:pointer;min-width:110px">
                                <i class="bi bi-mortarboard-fill fs-3 d-block mb-1"></i>
                                <span class="small fw-semibold">Graduado</span>
                            </div>

                            <div class="rol-card flex-fill p-3 rounded border text-center text-muted"
                                 data-rol="Docente" data-implementado="0" style="cursor:pointer;min-width:110px">
                                <i class="bi bi-person-badge-fill fs-3 d-block mb-1"></i>
                                <span class="small fw-semibold">Docente</span>
                            </div>

                        </div>

                        <input type="hidden" name="nombre_rol" id="nombre_rol" value="{{ old('nombre_rol') }}">

                        @error('nombre_rol')
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Sub-selector tipo docente --}}
                    <div class="mb-3 d-none" id="tipo-docente-section">
                        <label class="form-label fw-semibold">
                            Tipo de vinculación <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2 mb-2">
                            <div class="tipo-doc-card flex-fill p-2 rounded border text-center"
                                 data-tipo="Planta" style="cursor:pointer">
                                <i class="bi bi-building me-1"></i>
                                <span class="small fw-semibold">Planta</span>
                            </div>
                            <div class="tipo-doc-card flex-fill p-2 rounded border text-center"
                                 data-tipo="Cátedra" style="cursor:pointer">
                                <i class="bi bi-clock-history me-1"></i>
                                <span class="small fw-semibold">Cátedra</span>
                            </div>
                        </div>
                        <div class="alert alert-warning py-2 small mb-0">
                            <i class="bi bi-hourglass-split me-1"></i>
                            La carga de docentes estará disponible próximamente.
                        </div>
                    </div>

                    {{-- Período --}}
                    <div class="mb-3" id="campos-carga" style="display:none">
                        <label for="id_periodo" class="form-label fw-semibold">
                            Período <span class="text-danger">*</span>
                        </label>
                        <select id="id_periodo" name="id_periodo"
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

                    {{-- Archivo --}}
                    <div class="mb-3 d-none" id="campo-archivo">
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
                    <div class="alert alert-light border mb-3 d-none" id="formato-info">
                        <p class="small fw-semibold mb-1">
                            <i class="bi bi-info-circle me-1 text-primary"></i>Columnas esperadas:
                        </p>
                        <code class="small d-block text-wrap" style="font-size:.72rem">
                            DOCUMENTO ; NOMBRES ; APELLIDOS ; EMAIL ; SEDE ; NOMBRE DE LA SEDE ; PLAN ; PROGRAMA ACADEMICO ; FACULTAD
                        </code>
                        <p class="small text-muted mt-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                            El código de <strong>SEDE</strong> debe existir en el
                            <a href="{{ route('sedes.index') }}">módulo de Sedes</a>.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 d-none" id="btn-procesar">
                        <i class="bi bi-upload me-1"></i>
                        Procesar archivo — <span id="lbl-rol">Estudiante</span>
                    </button>

                </form>
            </x-card>
        </div>

        {{-- Resultado --}}
        @if(session('resultado'))
            @php $r = session('resultado'); @endphp
            <div class="col-md-6">
                <x-card title="Resultado — {{ session('nombre_rol') }}" color="success">

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

    <script>
        const rolCards       = document.querySelectorAll('.rol-card');
        const nombreRolInput = document.getElementById('nombre_rol');
        const btnProcesar    = document.getElementById('btn-procesar');
        const lblRol         = document.getElementById('lbl-rol');
        const camposCarga    = document.getElementById('campos-carga');
        const campoArchivo   = document.getElementById('campo-archivo');
        const formatoInfo    = document.getElementById('formato-info');
        const tipoDocenteSec = document.getElementById('tipo-docente-section');
        const tipoDocCards   = document.querySelectorAll('.tipo-doc-card');

        function seleccionarRol(rol, implementado) {
            rolCards.forEach(c => {
                c.classList.remove('border-primary', 'bg-primary', 'text-white');
                if (!c.dataset.implementado || c.dataset.implementado === '0') {
                    c.classList.add('text-muted');
                }
            });

            const card = document.querySelector(`.rol-card[data-rol="${rol}"]`);
            card.classList.add('border-primary', 'bg-primary', 'text-white');
            card.classList.remove('text-muted');

            nombreRolInput.value = rol;
            lblRol.textContent   = rol;

            if (rol === 'Docente') {
                tipoDocenteSec.classList.remove('d-none');
                camposCarga.style.display  = 'none';
                campoArchivo.classList.add('d-none');
                formatoInfo.classList.add('d-none');
                btnProcesar.classList.add('d-none');
            } else {
                tipoDocenteSec.classList.add('d-none');
                camposCarga.style.display  = '';
                campoArchivo.classList.remove('d-none');
                formatoInfo.classList.remove('d-none');
                btnProcesar.classList.remove('d-none');
            }
        }

        rolCards.forEach(card => {
            card.addEventListener('click', () => {
                seleccionarRol(card.dataset.rol, card.dataset.implementado);
            });
        });

        tipoDocCards.forEach(card => {
            card.addEventListener('click', () => {
                tipoDocCards.forEach(c => c.classList.remove('border-primary', 'bg-primary', 'text-white'));
                card.classList.add('border-primary', 'bg-primary', 'text-white');
            });
        });

        // Restaurar selección si hubo error de validación
        @if(old('nombre_rol'))
            seleccionarRol('{{ old('nombre_rol') }}', 1);
        @elseif(session('nombre_rol'))
            seleccionarRol('{{ session('nombre_rol') }}', 1);
        @endif
    </script>

</x-layout>
