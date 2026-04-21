<x-layout title="Carga Masiva de Usuarios">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-upload me-2" style="color:#196844"></i>Carga Masiva de Usuarios
        </h2>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-card title="Configurar carga" color="sibi">
                <form action="{{ route('usuarios.carga.store') }}" method="POST"
                      enctype="multipart/form-data" id="form-carga">
                    @csrf

                    {{-- Selector de tipo de usuario --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Tipo de usuario <span class="text-danger">*</span>
                        </label>

                        <div class="d-flex gap-2" style="overflow-x:auto;padding-bottom:2px">

                            <div class="rol-card p-3 rounded border text-center"
                                 data-rol="Estudiante" data-tipo="directo" style="cursor:pointer;flex:1 1 0;min-width:110px">
                                <i class="bi bi-person-fill fs-3 d-block mb-1"></i>
                                <span class="small fw-semibold">Estudiante</span>
                            </div>

                            <div class="rol-card p-3 rounded border text-center"
                                 data-rol="Graduado" data-tipo="directo" style="cursor:pointer;flex:1 1 0;min-width:110px">
                                <i class="bi bi-mortarboard-fill fs-3 d-block mb-1"></i>
                                <span class="small fw-semibold">Graduado</span>
                            </div>

                            <div class="rol-card p-3 rounded border text-center"
                                 data-rol="Docente" data-tipo="subselector" style="cursor:pointer;flex:1 1 0;min-width:110px">
                                <i class="bi bi-person-badge-fill fs-3 d-block mb-1"></i>
                                <span class="small fw-semibold">Docente</span>
                            </div>

                            <div class="rol-card p-3 rounded border text-center"
                                 data-rol="Empleado" data-tipo="subselector" style="cursor:pointer;flex:1 1 0;min-width:110px">
                                <i class="bi bi-briefcase-fill fs-3 d-block mb-1"></i>
                                <span class="small fw-semibold">Empleado</span>
                            </div>

                            <div class="rol-card p-3 rounded border text-center"
                                 data-rol="Familiar" data-tipo="directo" style="cursor:pointer;flex:1 1 0;min-width:110px">
                                <i class="bi bi-house-heart-fill fs-3 d-block mb-1"></i>
                                <span class="small fw-semibold">Familiar</span>
                            </div>

                        </div>

                        <input type="hidden" name="nombre_rol" id="nombre_rol" value="{{ old('nombre_rol') }}">

                        @error('nombre_rol')
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Sub-selector: Docente --}}
                    <div class="mb-3 d-none" id="tipo-docente-section">
                        <label class="form-label fw-semibold">
                            Tipo de vinculación <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2 mb-2">
                            <div class="subselector-card flex-fill p-2 rounded border text-center"
                                 data-subtipo="Planta" style="cursor:pointer">
                                <i class="bi bi-building me-1"></i>
                                <span class="small fw-semibold">Planta</span>
                            </div>
                            <div class="subselector-card flex-fill p-2 rounded border text-center"
                                 data-subtipo="Cátedra" style="cursor:pointer">
                                <i class="bi bi-clock-history me-1"></i>
                                <span class="small fw-semibold">Cátedra</span>
                            </div>
                        </div>
                        <div class="alert alert-warning py-2 small mb-0">
                            <i class="bi bi-hourglass-split me-1"></i>
                            La carga de docentes estará disponible próximamente.
                        </div>
                    </div>

                    {{-- Sub-selector: Empleado --}}
                    <div class="mb-3 d-none" id="tipo-empleado-section">
                        <label class="form-label fw-semibold">
                            Tipo de empleado <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2">
                            <div class="subselector-card flex-fill p-2 rounded border text-center text-muted"
                                 data-subtipo="Administrativo" data-implementado="0" style="cursor:pointer">
                                <i class="bi bi-person-gear me-1"></i>
                                <span class="small fw-semibold">Administrativo</span>
                            </div>
                            <div class="subselector-card flex-fill p-2 rounded border text-center"
                                 data-subtipo="Contratista" data-implementado="1" style="cursor:pointer">
                                <i class="bi bi-file-earmark-person me-1"></i>
                                <span class="small fw-semibold">Contratista</span>
                            </div>
                        </div>
                    </div>

                    {{-- Próximamente (Administrativo) --}}
                    <div class="d-none mb-3" id="proximamente-admin">
                        <div class="alert alert-warning py-2 small mb-0">
                            <i class="bi bi-hourglass-split me-1"></i>
                            La carga de administrativos estará disponible próximamente.
                        </div>
                    </div>

                    {{-- Período --}}
                    <div class="mb-3 d-none" id="campos-carga">
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

                    {{-- Formato: Estudiante / Graduado --}}
                    <div class="alert alert-light border mb-3 d-none" id="formato-estudiante">
                        <p class="small fw-semibold mb-1">
                            <i class="bi bi-info-circle me-1" style="color:#196844"></i>Columnas esperadas:
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

                    {{-- Formato: Familiar --}}
                    <div class="alert alert-light border mb-3 d-none" id="formato-familiar">
                        <p class="small fw-semibold mb-1">
                            <i class="bi bi-info-circle me-1" style="color:#196844"></i>Columnas esperadas:
                        </p>
                        <code class="small d-block text-wrap" style="font-size:.72rem">
                            DOCUMENTO ; NOMBRES ; APELLIDOS ; CORREO ; NOMBRE SEDE
                        </code>
                        <p class="small text-muted mt-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                            La <strong>SEDE</strong> se busca por nombre.
                        </p>
                    </div>

                    {{-- Formato: Contratista --}}
                    <div class="alert alert-light border mb-3 d-none" id="formato-contratista">
                        <p class="small fw-semibold mb-1">
                            <i class="bi bi-info-circle me-1" style="color:#196844"></i>Columnas esperadas:
                        </p>
                        <code class="small d-block text-wrap" style="font-size:.72rem">
                            DOCUMENTO ; NOMBRES ; APELLIDOS ; CORREO ; NOMBRE SEDE ; DEPENDENCIA
                        </code>
                        <p class="small text-muted mt-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                            La <strong>SEDE</strong> se busca por nombre. La <strong>DEPENDENCIA</strong>
                            se crea automáticamente si no existe.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-sibi w-100 d-none" id="btn-procesar">
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
                <x-card title="Resultado — {{ session('nombre_rol') }}" color="sibi">

                    <div class="row text-center g-3 mb-3">
                        <div class="col-4">
                            <div class="p-3 rounded" style="background:#e6f2ec">
                                <div class="fs-2 fw-bold" style="color:#196844">{{ $r['creados'] }}</div>
                                <div class="small text-muted">Nuevos</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded bg-light">
                                <div class="fs-2 fw-bold" style="color:#196844">{{ $r['actualizados'] }}</div>
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

    <style>
        .rol-card.sibi-selected,
        .subselector-card.sibi-selected {
            background-color: #196844 !important;
            border-color: #196844 !important;
            color: #fff !important;
        }
    </style>

    <script>
        const rolCards          = document.querySelectorAll('.rol-card');
        const nombreRolInput    = document.getElementById('nombre_rol');
        const btnProcesar       = document.getElementById('btn-procesar');
        const lblRol            = document.getElementById('lbl-rol');
        const camposCarga        = document.getElementById('campos-carga');
        const campoArchivo       = document.getElementById('campo-archivo');
        const formatoEstudiante  = document.getElementById('formato-estudiante');
        const formatoContratista = document.getElementById('formato-contratista');
        const formatoFamiliar    = document.getElementById('formato-familiar');
        const tipoDocenteSec     = document.getElementById('tipo-docente-section');
        const tipoEmpleadoSec    = document.getElementById('tipo-empleado-section');
        const proximamenteAdmin  = document.getElementById('proximamente-admin');

        function ocultarTodo() {
            tipoDocenteSec.classList.add('d-none');
            tipoEmpleadoSec.classList.add('d-none');
            proximamenteAdmin.classList.add('d-none');
            camposCarga.classList.add('d-none');
            campoArchivo.classList.add('d-none');
            formatoEstudiante.classList.add('d-none');
            formatoContratista.classList.add('d-none');
            formatoFamiliar.classList.add('d-none');
            btnProcesar.classList.add('d-none');
        }

        const formatoPorRol = {
            'Estudiante':  formatoEstudiante,
            'Graduado':    formatoEstudiante,
            'Familiar':    formatoFamiliar,
            'Contratista': formatoContratista,
        };

        function seleccionarRol(rol) {
            rolCards.forEach(c => c.classList.remove('sibi-selected'));
            document.querySelector(`.rol-card[data-rol="${rol}"]`)?.classList.add('sibi-selected');

            ocultarTodo();
            nombreRolInput.value = '';
            lblRol.textContent = rol;

            if (rol === 'Docente') {
                tipoDocenteSec.classList.remove('d-none');
            } else if (rol === 'Empleado') {
                tipoEmpleadoSec.classList.remove('d-none');
            } else {
                nombreRolInput.value = rol;
                camposCarga.classList.remove('d-none');
                campoArchivo.classList.remove('d-none');
                (formatoPorRol[rol] ?? formatoEstudiante).classList.remove('d-none');
                btnProcesar.classList.remove('d-none');
            }
        }

        function seleccionarSubtipo(subtipo) {
            document.querySelectorAll('.subselector-card').forEach(c => c.classList.remove('sibi-selected'));
            const card = document.querySelector(`.subselector-card[data-subtipo="${subtipo}"]`);
            if (card) card.classList.add('sibi-selected');

            proximamenteAdmin.classList.add('d-none');
            camposCarga.classList.add('d-none');
            campoArchivo.classList.add('d-none');
            formatoEstudiante.classList.add('d-none');
            formatoContratista.classList.add('d-none');
            btnProcesar.classList.add('d-none');
            nombreRolInput.value = '';

            if (subtipo === 'Contratista') {
                nombreRolInput.value = 'Contratista';
                lblRol.textContent   = 'Contratistas';
                camposCarga.classList.remove('d-none');
                campoArchivo.classList.remove('d-none');
                formatoContratista.classList.remove('d-none');
                btnProcesar.classList.remove('d-none');
            } else if (subtipo === 'Administrativo') {
                proximamenteAdmin.classList.remove('d-none');
            }
        }

        rolCards.forEach(card => {
            card.addEventListener('click', () => seleccionarRol(card.dataset.rol));
        });

        document.querySelectorAll('.subselector-card').forEach(card => {
            card.addEventListener('click', () => seleccionarSubtipo(card.dataset.subtipo));
        });

        // Restaurar selección si hubo error de validación o resultado previo
        @if(old('nombre_rol') || session('nombre_rol'))
            @php $rolRestaurar = old('nombre_rol') ?: session('nombre_rol'); @endphp
            @if($rolRestaurar === 'Contratista')
                seleccionarRol('Empleado');
                seleccionarSubtipo('Contratista');
            @else
                seleccionarRol('{{ $rolRestaurar }}');
            @endif
        @endif
    </script>

</x-layout>
