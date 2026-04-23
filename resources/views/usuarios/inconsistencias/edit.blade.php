<x-layout title="Corregir Inconsistencia">

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">

            {{-- ── Error detectado ── --}}
            @php
                $campo = $inconsistencia->campoError();
            @endphp

            <div class="alert alert-danger d-flex gap-2 align-items-start mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill fs-5 flex-shrink-0 mt-1"></i>
                <div>
                    <div class="fw-semibold mb-1">Error detectado</div>
                    <div class="small">{{ $inconsistencia->error }}</div>
                    @if($inconsistencia->fila)
                        <div class="text-muted small mt-1">
                            Fila {{ $inconsistencia->fila }} del archivo CSV original
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Formulario de corrección ── --}}
            <x-card title="Corregir y guardar registro" color="warning">

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                        <i class="bi bi-x-octagon me-1"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('usuarios.inconsistencias.update', $inconsistencia) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    {{-- ── Período ── --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Período <span class="text-danger">*</span>
                        </label>
                        <select name="id_periodo"
                                class="form-select {{ $errors->has('id_periodo') ? 'is-invalid' : '' }}">
                            <option value="">— Seleccione —</option>
                            @foreach($periodos as $p)
                                <option value="{{ $p->id_periodo }}"
                                    {{ old('id_periodo', $inconsistencia->id_periodo) == $p->id_periodo ? 'selected' : '' }}>
                                    {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_periodo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Datos del estudiante ── --}}
                    <p class="fw-semibold text-muted small text-uppercase mt-3 mb-2">
                        <i class="bi bi-person me-1"></i>Datos del estudiante
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">
                                Documento <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="documento"
                                   value="{{ old('documento', $inconsistencia->documento) }}"
                                   maxlength="20"
                                   class="form-control form-control-sm
                                          {{ $campo === 'documento' ? 'is-invalid border-danger' : '' }}
                                          {{ $errors->has('documento') ? 'is-invalid' : '' }}">
                            @if($campo === 'documento')
                                <div class="text-danger small mt-1">
                                    <i class="bi bi-arrow-up-circle me-1"></i>Campo señalado por el error
                                </div>
                            @endif
                            @error('documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">
                                Nombres <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombres"
                                   value="{{ old('nombres', $inconsistencia->nombres) }}"
                                   maxlength="100"
                                   class="form-control form-control-sm {{ $errors->has('nombres') ? 'is-invalid' : '' }}">
                            @error('nombres')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">
                                Apellidos <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="apellidos"
                                   value="{{ old('apellidos', $inconsistencia->apellidos) }}"
                                   maxlength="100"
                                   class="form-control form-control-sm {{ $errors->has('apellidos') ? 'is-invalid' : '' }}">
                            @error('apellidos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-12">
                            <label class="form-label small fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="email"
                                   value="{{ old('email', $inconsistencia->email) }}"
                                   maxlength="100"
                                   class="form-control form-control-sm
                                          {{ $campo === 'email' ? 'border-danger' : '' }}
                                          {{ $errors->has('email') ? 'is-invalid' : '' }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if(in_array($inconsistencia->nombre_rol, ['Contratista', 'Familiar', 'Administrativo', 'Docente']))

                    {{-- ── Sede (contratista: por nombre) ── --}}
                    <p class="fw-semibold text-muted small text-uppercase mt-3 mb-2">
                        <i class="bi bi-geo-alt me-1"></i>Sede
                        @if($campo === 'nombre_sede')
                            <span class="badge bg-danger ms-1" style="font-size:.65rem">
                                <i class="bi bi-exclamation-circle me-1"></i>Campo con error
                            </span>
                        @endif
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-12">
                            <label class="form-label small fw-semibold">
                                Sede <span class="text-danger">*</span>
                            </label>
                            <select name="nombre_sede"
                                    class="form-select form-select-sm
                                           {{ $campo === 'nombre_sede' ? 'is-invalid border-danger' : '' }}
                                           {{ $errors->has('nombre_sede') ? 'is-invalid' : '' }}">
                                <option value="">— Seleccione una sede —</option>
                                @foreach($sedes as $sede)
                                    <option value="{{ $sede->nombre }}"
                                        {{ old('nombre_sede', $inconsistencia->nombre_sede) === $sede->nombre ? 'selected' : '' }}>
                                        {{ $sede->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nombre_sede')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if(in_array($inconsistencia->nombre_rol, ['Contratista', 'Administrativo', 'Docente']))
                    {{-- ── Dependencia ── --}}
                    <p class="fw-semibold text-muted small text-uppercase mt-3 mb-2">
                        <i class="bi bi-diagram-3 me-1"></i>Dependencia
                        @if($campo === 'dependencia')
                            <span class="badge bg-danger ms-1" style="font-size:.65rem">
                                <i class="bi bi-exclamation-circle me-1"></i>Campo con error
                            </span>
                        @endif
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-12">
                            <input type="text" name="dependencia"
                                   value="{{ old('dependencia', $inconsistencia->dependencia) }}"
                                   maxlength="200"
                                   class="form-control form-control-sm
                                          {{ $campo === 'dependencia' ? 'border-danger' : '' }}
                                          {{ $errors->has('dependencia') ? 'is-invalid' : '' }}">
                            @error('dependencia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="dependencia" value="">
                    @endif

                    @if(in_array($inconsistencia->nombre_rol, ['Administrativo', 'Docente']))
                    {{-- ── Cargo ── --}}
                    <p class="fw-semibold text-muted small text-uppercase mt-3 mb-2">
                        <i class="bi bi-person-badge me-1"></i>Cargo
                        @if($campo === 'nombre_cargo')
                            <span class="badge bg-danger ms-1" style="font-size:.65rem">
                                <i class="bi bi-exclamation-circle me-1"></i>Campo con error
                            </span>
                        @endif
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">
                                Código del cargo <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="codigo_cargo"
                                   value="{{ old('codigo_cargo', $inconsistencia->codigo_cargo) }}"
                                   maxlength="30"
                                   class="form-control form-control-sm {{ $errors->has('codigo_cargo') ? 'is-invalid' : '' }}">
                            @error('codigo_cargo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label small fw-semibold">
                                Nombre del cargo <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre_cargo"
                                   value="{{ old('nombre_cargo', $inconsistencia->nombre_cargo) }}"
                                   maxlength="150"
                                   class="form-control form-control-sm
                                          {{ $campo === 'nombre_cargo' ? 'border-danger' : '' }}
                                          {{ $errors->has('nombre_cargo') ? 'is-invalid' : '' }}">
                            @error('nombre_cargo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="codigo_cargo" value="">
                    <input type="hidden" name="nombre_cargo" value="">
                    @endif

                    {{-- Campos ocultos no usados --}}
                    <input type="hidden" name="codigo_sede"     value="">
                    <input type="hidden" name="codigo_plan"     value="">
                    <input type="hidden" name="nombre_programa" value="">
                    <input type="hidden" name="nombre_facultad" value="">

                    @else

                    {{-- ── Sede (estudiante/graduado: por código) ── --}}
                    <p class="fw-semibold text-muted small text-uppercase mt-3 mb-2">
                        <i class="bi bi-geo-alt me-1"></i>Sede
                        @if($campo === 'codigo_sede')
                            <span class="badge bg-danger ms-1" style="font-size:.65rem">
                                <i class="bi bi-exclamation-circle me-1"></i>Campo con error
                            </span>
                        @endif
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-5">
                            <label class="form-label small fw-semibold">
                                Sede <span class="text-danger">*</span>
                            </label>
                            <select name="codigo_sede" id="selectSede"
                                    class="form-select form-select-sm
                                           {{ $campo === 'codigo_sede' ? 'is-invalid border-danger' : '' }}
                                           {{ $errors->has('codigo_sede') ? 'is-invalid' : '' }}"
                                    onchange="rellenarNombreSede(this)">
                                <option value="">— Seleccione una sede existente —</option>
                                @foreach($sedes as $sede)
                                    <option value="{{ $sede->codigo }}"
                                            data-nombre="{{ $sede->nombre }}"
                                        {{ old('codigo_sede', $inconsistencia->codigo_sede) === $sede->codigo ? 'selected' : '' }}>
                                        [{{ $sede->codigo }}] {{ $sede->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @if($campo === 'codigo_sede')
                                <div class="invalid-feedback d-block text-danger small">
                                    <i class="bi bi-arrow-up-circle me-1"></i>
                                    Seleccione una sede válida de la lista
                                </div>
                            @endif
                            @error('codigo_sede')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-7">
                            <label class="form-label small fw-semibold">
                                Nombre de sede <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre_sede" id="inputNombreSede"
                                   value="{{ old('nombre_sede', $inconsistencia->nombre_sede) }}"
                                   maxlength="100" readonly
                                   class="form-control form-control-sm bg-light {{ $errors->has('nombre_sede') ? 'is-invalid' : '' }}">
                            @error('nombre_sede')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ── Programa académico ── --}}
                    <p class="fw-semibold text-muted small text-uppercase mt-3 mb-2">
                        <i class="bi bi-journal-bookmark me-1"></i>Programa académico
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-12">
                            <label class="form-label small fw-semibold">
                                Facultad <span class="text-danger">*</span>
                                @if($campo === 'nombre_facultad')
                                    <span class="badge bg-danger ms-1" style="font-size:.6rem">Error</span>
                                @endif
                            </label>
                            <input type="text" name="nombre_facultad"
                                   value="{{ old('nombre_facultad', $inconsistencia->nombre_facultad) }}"
                                   maxlength="200"
                                   class="form-control form-control-sm
                                          {{ $campo === 'nombre_facultad' ? 'border-danger' : '' }}
                                          {{ $errors->has('nombre_facultad') ? 'is-invalid' : '' }}">
                            @error('nombre_facultad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label small fw-semibold">
                                Programa <span class="text-danger">*</span>
                                @if($campo === 'nombre_programa')
                                    <span class="badge bg-danger ms-1" style="font-size:.6rem">Error</span>
                                @endif
                            </label>
                            <input type="text" name="nombre_programa"
                                   value="{{ old('nombre_programa', $inconsistencia->nombre_programa) }}"
                                   maxlength="200"
                                   class="form-control form-control-sm
                                          {{ $campo === 'nombre_programa' ? 'border-danger' : '' }}
                                          {{ $errors->has('nombre_programa') ? 'is-invalid' : '' }}">
                            @error('nombre_programa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold">
                                Código de plan <span class="text-danger">*</span>
                                @if($campo === 'codigo_plan')
                                    <span class="badge bg-danger ms-1" style="font-size:.6rem">Error</span>
                                @endif
                            </label>
                            <input type="text" name="codigo_plan"
                                   value="{{ old('codigo_plan', $inconsistencia->codigo_plan) }}"
                                   maxlength="20"
                                   class="form-control form-control-sm
                                          {{ $campo === 'codigo_plan' ? 'border-danger' : '' }}
                                          {{ $errors->has('codigo_plan') ? 'is-invalid' : '' }}">
                            @error('codigo_plan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Campo oculto no usado por estudiantes --}}
                    <input type="hidden" name="dependencia" value="">

                    @endif

                    {{-- ── Acciones ── --}}
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="bi bi-check-lg me-1"></i>Guardar registro corregido
                        </button>
                        <a href="{{ route('usuarios.inconsistencias.index') }}"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>

                </form>
            </x-card>

        </div>
    </div>

<script>
    function rellenarNombreSede(sel) {
        const opt    = sel.options[sel.selectedIndex];
        const nombre = opt.dataset.nombre ?? '';
        document.getElementById('inputNombreSede').value = nombre;
    }
</script>

</x-layout>
