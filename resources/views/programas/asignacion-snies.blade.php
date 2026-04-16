<x-layout title="Asignación SNIES y Tipo de Formación">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="bi bi-card-checklist me-2" style="color:#196844"></i>Asignación SNIES y Tipo de Formación
        </h2>
        <a href="{{ route('programas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a Programas
        </a>
    </div>

    @if($errors->any())
        <x-alert type="danger" message="{{ implode(' | ', $errors->all()) }}" />
    @endif

    @if($programas->isEmpty())
        <x-card>
            <p class="text-center text-muted mb-0 py-3">
                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                No hay programas registrados.
            </p>
        </x-card>
    @else
        <form action="{{ route('programas.asignacion-snies.guardar') }}" method="POST">
            @csrf

            <p class="text-muted small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Solo se muestran las sedes ya asociadas a cada programa. Para agregar o quitar sedes, use la opción
                <a href="{{ route('programas.index') }}">Editar programa</a>.
            </p>

            @foreach($facultades as $idFacultad => $facultad)
                @if($programas->has($idFacultad))
                    <div class="d-flex align-items-center gap-2 mt-3 mb-2">
                        <span class="badge px-3 py-2 fs-6" style="background-color:#ffc107;color:#000">
                            <i class="bi bi-building me-1"></i>{{ $facultad->nombre }}
                        </span>
                    </div>

                    @foreach($programas[$idFacultad] as $programa)
                        @php
                            $sinTipo  = $programa->id_tipo_formacion === null;
                            $sinSnies = $programa->sedes->contains(fn($s) => empty($s->pivot->codigo_snies));
                            $incompleto = $sinTipo || $sinSnies;
                        @endphp
                        <div class="card shadow-sm mb-3 {{ $incompleto ? 'border-warning' : '' }}">
                            <div class="card-body py-3">

                                {{-- Nombre del programa --}}
                                <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                                    <i class="bi bi-journal-text me-1 text-secondary"></i>{{ $programa->nombre }}
                                    @if($sinTipo)
                                        <span class="badge bg-warning text-dark" style="font-size:.65rem">Sin tipo de formación</span>
                                    @endif
                                    @if($sinSnies)
                                        <span class="badge bg-warning text-dark" style="font-size:.65rem">Sin SNIES</span>
                                    @endif
                                </div>

                                <div class="row g-3 align-items-start">

                                    {{-- Tipo de formación --}}
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold mb-1">
                                            Tipo de formación
                                            <span class="text-muted fw-normal">(opcional)</span>
                                        </label>
                                        <select name="tipo_formacion[{{ $programa->id_programa }}]"
                                                class="form-select form-select-sm {{ $errors->has('tipo_formacion.' . $programa->id_programa) ? 'is-invalid' : '' }}">
                                            <option value="">-- Sin especificar --</option>
                                            @foreach($niveles as $nivel)
                                                <optgroup label="{{ $nivel->nombre }}">
                                                    @foreach($nivel->tiposFormacion as $tipo)
                                                        <option value="{{ $tipo->id_tipo_formacion }}"
                                                            {{ old('tipo_formacion.' . $programa->id_programa, $programa->id_tipo_formacion) == $tipo->id_tipo_formacion ? 'selected' : '' }}>
                                                            {{ $tipo->nombre }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        @error('tipo_formacion.' . $programa->id_programa)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- SNIES por sede --}}
                                    <div class="col-md-8">
                                        <label class="form-label small fw-semibold mb-1">
                                            Código SNIES por sede
                                        </label>

                                        @if($programa->sedes->isEmpty())
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-dash-circle me-1"></i>Sin sedes asociadas.
                                            </p>
                                        @else
                                            <div class="border rounded-3" style="overflow:hidden;">
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0" style="margin-bottom:0!important">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width:90px">Código</th>
                                                            <th>Sede</th>
                                                            <th style="width:160px">Código SNIES</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($programa->sedes as $sede)
                                                            <tr>
                                                                <td class="align-middle">
                                                                    <span class="badge bg-secondary">{{ $sede->codigo }}</span>
                                                                </td>
                                                                <td class="align-middle small">{{ $sede->nombre }}</td>
                                                                <td class="align-middle">
                                                                    <input
                                                                        type="text"
                                                                        name="codigo_snies[{{ $programa->id_programa }}][{{ $sede->id_sede }}]"
                                                                        maxlength="20"
                                                                        placeholder="Opcional"
                                                                        value="{{ old('codigo_snies.' . $programa->id_programa . '.' . $sede->id_sede, $sede->pivot->codigo_snies) }}"
                                                                        class="form-control form-control-sm {{ $errors->has('codigo_snies.' . $programa->id_programa . '.' . $sede->id_sede) ? 'is-invalid' : '' }}"
                                                                    >
                                                                    @error('codigo_snies.' . $programa->id_programa . '.' . $sede->id_sede)
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-sibi">
                    <i class="bi bi-save me-1"></i> Guardar cambios
                </button>
                <a href="{{ route('programas.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>

        </form>
    @endif

</x-layout>
